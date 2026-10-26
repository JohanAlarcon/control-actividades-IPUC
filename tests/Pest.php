<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Activity;
use App\Models\Approval;
use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\Site;
use App\Models\Sympathizer;
use App\Models\User;
use App\Support\Approvals\ApprovalFlow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Illuminate\View\View;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

// Las pruebas de Unit tambien necesitan la base: los calculos de este proyecto
// (avance, compromiso, saldo) se arman desde filas relacionadas.
pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Unit', 'Architecture');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Una sede con los tres niveles y una persona en cada cargo. La usan las pruebas de la
 * estructura de comites. Los nombres son ficticios, como exige la constitucion.
 */
function estructuraDePrueba(): array
{
    $sede = Site::factory()->create();
    $comite = fn (string $nombre, ?Committee $padre = null) => Committee::factory()
        ->for($sede)
        ->when($padre, fn ($fabrica) => $fabrica->childOf($padre))
        ->create(['name' => $nombre]);

    $alabanza = $comite('Comité de Alabanza');
    $sonido = $comite('Sub-comité de Sonido', $alabanza);
    $montaje = $comite('Equipo de Montaje', $sonido);
    $coro = $comite('Sub-comité de Coro', $alabanza);
    $damas = $comite('Comité de Damas Dorcas');

    $persona = function (string $nombre, Committee $donde, string $cargo) use ($sede) {
        $usuario = User::factory()->create(['site_id' => $sede->id, 'name' => $nombre]);
        CommitteeUser::factory()->forCommittee($donde)->role($cargo)->create(['user_id' => $usuario->id]);

        return $usuario;
    };

    return [
        'sede' => $sede,
        'alabanza' => $alabanza,
        'sonido' => $sonido,
        'montaje' => $montaje,
        'coro' => $coro,
        'damas' => $damas,
        'pastor' => User::factory()->create(['site_id' => $sede->id, 'is_pastor' => true]),
        'presidente' => $persona('Hermano Rigoberto Páez', $alabanza, 'president'),
        'secretaria' => $persona('Hermana Luz Mary Quintero', $alabanza, 'secretary'),
        'tesorero' => $persona('Hermano Jairo Betancur', $alabanza, 'treasurer'),
        'liderSonido' => $persona('Hermano Andrés Molina', $sonido, 'leader'),
        'liderMontaje' => $persona('Hermano Wilson Garzón', $montaje, 'leader'),
        'liderCoro' => $persona('Hermana Omaira Lozano', $coro, 'leader'),
        'presidentaDamas' => $persona('Hermana Blanca Cárdenas', $damas, 'president'),
    ];
}

/** Lo que manda el navegador al pedir una propiedad diferida, version incluida: sin ella Inertia responde 409. */
function pedidoParcial(string $propiedad, string $pantalla = 'dashboard'): array
{
    return [
        'X-Inertia' => 'true',
        'X-Inertia-Version' => (string) app(HandleInertiaRequests::class)->version(request()),
        'X-Inertia-Partial-Component' => $pantalla,
        'X-Inertia-Partial-Data' => $propiedad,
    ];
}

/*
 * Los datos de una pantalla viajan dentro del HTML con las tildes escapadas: «Comité»
 * llega como «Comité». Con assertSee no se encuentra nunca, y una prueba de que
 * algo NO aparece pasaria siempre, aunque el dato estuviera ahi. Estas dos buscan en lo
 * que la pantalla realmente recibe. PageTextTest demuestra la diferencia.
 */
function textoDeLaPagina(TestResponse $respuesta): string
{
    $original = $respuesta->baseResponse->original ?? null;

    if ($original instanceof View && isset($original->getData()['page'])) {
        return json_encode($original->getData()['page'], JSON_UNESCAPED_UNICODE);
    }

    $json = json_decode((string) $respuesta->getContent(), true);

    return is_array($json) ? json_encode($json, JSON_UNESCAPED_UNICODE) : (string) $respuesta->getContent();
}

TestResponse::macro('assertShows', function (string $texto) {
    Assert::assertStringContainsString($texto, textoDeLaPagina($this), "La pantalla no muestra «{$texto}».");

    return $this;
});

TestResponse::macro('assertDoesntShow', function (string $texto) {
    Assert::assertStringNotContainsString($texto, textoDeLaPagina($this), "La pantalla muestra «{$texto}» y no debería.");

    return $this;
});

/** Un nodo del arbol de comites, buscado por su nombre en cualquier nivel. */
function nodoDelArbol(array $arbol, string $nombre): ?array
{
    foreach ($arbol as $nodo) {
        if ($nodo['name'] === $nombre) {
            return $nodo;
        }

        if ($encontrado = nodoDelArbol($nodo['children'], $nombre)) {
            return $encontrado;
        }
    }

    return null;
}

/**
 * Lo que pide esa persona, por el camino real: ApprovalFlow decide a quien le llega. Todas apuntan
 * a una actividad de su comite, el unico objeto que ya existe; el gasto y la tarea tendran el suyo
 * cuando existan sus modulos.
 */
function solicitudDe(User $quien, string $tipo = 'activity', string $titulo = 'Vigilia de jóvenes', ?float $monto = null): Approval
{
    $cargo = CommitteeUser::where('user_id', $quien->id)->sole();
    $actividad = Activity::factory()
        ->forCommittee($cargo->committee)
        ->create(['title' => $titulo, 'proposed_by_user_id' => $quien->id]);

    return app(ApprovalFlow::class)->request($cargo, $actividad, $tipo, $titulo, null, $monto);
}

/**
 * El comite que acompaña a los simpatizantes, con su presidente, su secretario y el lider de un
 * sub-comite: los cargos que la policy mas estrecha del proyecto debe distinguir (RF-040, R-06).
 * Los nombres son ficticios, como exige la constitucion.
 */
function evangelismoDePrueba(array $e): array
{
    $evangelismo = Committee::factory()->for($e['sede'])->create(['name' => 'Comité de Evangelismo', 'follows_sympathizers' => true]);
    $visitacion = Committee::factory()->for($e['sede'])->childOf($evangelismo)->create(['name' => 'Sub-comité de Visitación']);

    $persona = function (string $nombre, Committee $donde, string $cargo) use ($e) {
        $usuario = User::factory()->create(['site_id' => $e['sede']->id, 'name' => $nombre]);
        CommitteeUser::factory()->forCommittee($donde)->role($cargo)->create(['user_id' => $usuario->id]);

        return $usuario;
    };

    return [
        'evangelismo' => $evangelismo,
        'visitacion' => $visitacion,
        'presidenteEvangelismo' => $persona('Hermano Gustavo Rengifo', $evangelismo, 'president'),
        'secretarioEvangelismo' => $persona('Hermano Esteban Lara', $evangelismo, 'secretary'),
        'liderVisitacion' => $persona('Hermana Rocío Gómez', $visitacion, 'leader'),
    ];
}

/**
 * Un simpatizante con su camino hasta la etapa dada —cada etapa con su fecha— y sus contactos,
 * contados en dias hacia atras desde hoy.
 */
function simpatizanteDePrueba(array $ev, array $datos = [], int $etapa = 1, array $contactosHace = []): Sympathizer
{
    $quien = $ev['presidenteEvangelismo'];
    $persona = Sympathizer::factory()->forCommittee($ev['evangelismo'])->create($datos + ['stage' => $etapa, 'first_contact_at' => now()->subDays(100)->toDateString()]);

    for ($n = 1; $n <= $etapa; $n++) {
        $persona->stages()->create(['site_id' => $persona->site_id, 'stage' => $n, 'reached_at' => now()->subDays(100 - $n * 10)->toDateString(), 'recorded_by_user_id' => $quien->id]);
    }

    foreach ($contactosHace as $dias) {
        $persona->visits()->create(['site_id' => $persona->site_id, 'visited_at' => now()->subDays($dias)->toDateString(), 'type' => 'visit', 'by_user_id' => $quien->id]);
    }

    return $persona;
}
