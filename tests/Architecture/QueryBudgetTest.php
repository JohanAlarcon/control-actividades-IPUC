<?php

use App\Models\Activity;
use App\Models\Approval;
use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/**
 * Puerta de calidad 5: ninguna pantalla hace mas de cinco consultas propias.
 *
 * Se cuentan las consultas de la pantalla: ResolveScope, HandleInertiaRequests,
 * policies y controlador. Quedan fuera las tres fijas de Laravel en cada
 * peticion -leer la sesion, cargar a la persona y guardar la sesion-, que se
 * miden aparte. Se mide como tres personas reales y con datos de verdad, porque
 * una cuenta sin cargos sobre una base vacia no ejercita casi nada.
 */
const TECHO_DE_CONSULTAS = 5;

const TECHO_DEL_BLOQUE_COMPARTIDO = 3;

const CONSULTAS_FIJAS_DE_LARAVEL = 3;

/** Rutas que no sirven una pantalla con datos de la congregacion. */
const RUTAS_FUERA_DE_MEDICION = [
    'login',
    'register',
    'password.request',
    'password.reset',
    'verification.notice',
    'storage.local',
];

/** Pantallas con parametros: cada una se mide en la prueba de su pantalla, con datos reales. */
const MEDIDAS_EN_SU_PRUEBA = [
    'users.edit' => 'tests/Feature/Users/UserManagementTest.php',
    'committees.show' => 'tests/Feature/Committees/CommitteeShowTest.php',
];

function rutasQueSirvenPantalla(): array
{
    $rutas = [];

    foreach (Route::getRoutes() as $ruta) {
        $nombre = $ruta->getName();

        if (! in_array('GET', $ruta->methods(), true) || str_contains($ruta->uri(), '{')) {
            continue;
        }
        if ($nombre === null || in_array($nombre, RUTAS_FUERA_DE_MEDICION, true)) {
            continue;
        }

        $rutas[$nombre] = '/'.ltrim($ruta->uri(), '/');
    }

    return $rutas;
}

/** Una sede con los tres niveles, una persona en cada tipo de cargo y algo de datos en cada nivel. */
function congregacionDePrueba(): array
{
    $sede = Site::factory()->create();
    $alabanza = Committee::factory()->for($sede)->create();
    $sonido = Committee::factory()->for($sede)->childOf($alabanza)->create();
    $montaje = Committee::factory()->for($sede)->childOf($sonido)->create();

    $presidente = User::factory()->create(['site_id' => $sede->id]);
    CommitteeUser::factory()->forCommittee($alabanza)->create(['user_id' => $presidente->id]);
    $lider = User::factory()->create(['site_id' => $sede->id]);
    CommitteeUser::factory()->forCommittee($sonido)->role(CommitteeUser::LEADER)->create(['user_id' => $lider->id]);

    foreach ([$alabanza, $sonido, $montaje] as $comite) {
        Activity::factory()->forCommittee($comite)->create();
        Approval::factory()->forCommittee($comite)->create();
    }

    return [
        'sede' => $sede,
        'alabanza' => $alabanza,
        'sonido' => $sonido,
        'personas' => [
            'pastor' => User::factory()->create(['site_id' => $sede->id, 'is_pastor' => true]),
            'presidente' => $presidente,
            'líder' => $lider,
        ],
    ];
}

/** Diez veces mas de todo, dentro y fuera del ambito de cada persona. */
function multiplicarDatos(array $congregacion): void
{
    foreach (range(1, 10) as $vuelta) {
        $comite = Committee::factory()->for($congregacion['sede'])->create();
        $dentro = Committee::factory()->for($congregacion['sede'])->childOf($congregacion['sonido'])->create();

        foreach ([$comite, $dentro, $congregacion['alabanza']] as $donde) {
            Activity::factory()->forCommittee($donde)->create();
            Approval::factory()->forCommittee($donde)->create();
        }

        $persona = User::factory()->create(['site_id' => $congregacion['sede']->id]);
        CommitteeUser::factory()->forCommittee($comite)->create(['user_id' => $persona->id]);
    }
}

function contarConsultas(Closure $peticion): array
{
    DB::flushQueryLog();
    DB::enableQueryLog();
    $respuesta = $peticion();
    $consultas = count(DB::getQueryLog());
    DB::disableQueryLog();

    return [$consultas, $respuesta->status()];
}

/** @return array<string, int> consultas por "persona en ruta" */
function medirTodo($prueba, array $personas): array
{
    $medidas = [];

    foreach (rutasQueSirvenPantalla() as $nombre => $uri) {
        foreach ($personas as $quien => $persona) {
            [$consultas, $estado] = contarConsultas(fn () => $prueba->actingAs($persona)->get($uri));

            expect($estado)->toBeLessThan(500, "{$quien} en {$uri} respondió con error {$estado}");
            $medidas["{$quien} en {$nombre} ({$uri})"] = $consultas;
        }
    }

    return $medidas;
}

it('ninguna pantalla pasa de cinco consultas propias, para ninguna de las tres personas', function () {
    $medidas = medirTodo($this, congregacionDePrueba()['personas']);

    $excedidas = array_filter($medidas, fn ($n) => $n > TECHO_DE_CONSULTAS);

    expect($excedidas)->toBeEmpty(
        'Estas pantallas pasan de '.TECHO_DE_CONSULTAS.' consultas: '.json_encode($excedidas, JSON_UNESCAPED_UNICODE)
    );
});

it('el número de consultas no crece cuando crecen los datos', function () {
    $congregacion = congregacionDePrueba();
    $antes = medirTodo($this, $congregacion['personas']);

    multiplicarDatos($congregacion);
    $despues = medirTodo($this, $congregacion['personas']);

    $crecieron = array_filter($despues, fn ($n, $donde) => $n !== $antes[$donde], ARRAY_FILTER_USE_BOTH);

    expect($crecieron)->toBeEmpty(
        'Estas pantallas hacen más consultas con más datos (consulta dentro de un bucle): '.json_encode($crecieron, JSON_UNESCAPED_UNICODE)
    );
});

it('el bloque compartido no pasa de tres consultas', function () {
    Route::middleware(['web', 'auth'])->get('/_bloque-compartido', fn () => Inertia::render('dashboard'));
    $congregacion = congregacionDePrueba();

    foreach ($congregacion['personas'] as $quien => $persona) {
        [$consultas] = contarConsultas(fn () => $this->actingAs($persona)->get('/_bloque-compartido'));

        expect($consultas)->toBeLessThanOrEqual(TECHO_DEL_BLOQUE_COMPARTIDO, "El bloque compartido de {$quien} cuesta {$consultas}");
    }
});

it('lo fijo de Laravel por petición son tres consultas, y se mide aparte', function () {
    $presidente = congregacionDePrueba()['personas']['presidente'];
    $presidente->update(['username' => 'presidente.medicion', 'password' => Hash::make('clave1234')]);

    [$propias] = contarConsultas(fn () => $this->actingAs($presidente)->get('/dashboard'));

    // Ahora como en el servidor: sesiones en la base y una entrada de verdad. La limpieza de
    // sesiones vencidas corre al azar en 2 de cada 100 peticiones y suma una consulta: se apaga,
    // porque lo que se mide aqui es lo que pagan todas las peticiones.
    config(['session.driver' => 'database', 'session.lottery' => [0, 100]]);
    app('auth')->forgetGuards();
    $this->post('/login', ['username' => 'presidente.medicion', 'password' => 'clave1234']);
    app('auth')->forgetGuards();

    [$reales] = contarConsultas(fn () => $this->get('/dashboard'));

    expect($reales - $propias)->toBe(CONSULTAS_FIJAS_DE_LARAVEL);
});

it('toda pantalla con parámetros tiene su medición en la prueba de esa pantalla', function () {
    $sinMedir = [];

    foreach (Route::getRoutes() as $ruta) {
        $nombre = $ruta->getName();

        if (! in_array('GET', $ruta->methods(), true) || ! str_contains($ruta->uri(), '{')) {
            continue;
        }
        if (in_array($nombre, RUTAS_FUERA_DE_MEDICION, true)) {
            continue;
        }

        $archivo = MEDIDAS_EN_SU_PRUEBA[$nombre] ?? null;

        if (! $archivo || ! file_exists(base_path($archivo))) {
            $sinMedir[] = ($nombre ?? 'sin nombre').' ('.$ruta->uri().')';
        }
    }

    expect($sinMedir)->toBeEmpty('Pantallas con parámetros sin medición: '.implode(' · ', $sinMedir));
});

it('la lista de pantallas sale de las rutas, no de un número escrito a mano', function () {
    expect(rutasQueSirvenPantalla())->not->toBeEmpty();
});
