<?php

use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\Approval;
use App\Models\CommitteeUser;
use App\Models\Schedule;
use App\Support\Approvals\ApprovalFlow;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Los cronogramas internos y sus actividades (T225; HU-27, RF-086, RF-087). Los ve el comité dueño,
 * sus ascendientes y el pastor; los crea y los llena su presidente o su líder. Sus actividades no
 * pasan por aprobación y se cruzan con todo. El escenario 3, lo que se repite, llega con T226.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-03-10 10:00', 'America/Bogota'));
    $this->periodo = ['starts_on' => '2026-02-01', 'ends_on' => '2026-06-30'];
    $this->actividad = [
        'title' => 'Ensayo general',
        'activity_type_id' => ActivityType::where('site_id', $this->e['sede']->id)->where('name', 'Ensayo')->value('id'),
        'date' => '2026-04-18',
        'time' => '19:00',
        'ends' => '21:00',
        'location' => 'Templo central',
        'theme' => 'Cantad alegres a Dios',
        'responsible' => 'La directora del coro',
    ];
    $this->ensayos = fn () => Schedule::factory()->internalOf($this->e['alabanza'], 'Ensayos del semestre')->create($this->periodo);
});

test('escenario 1: el presidente crea un cronograma interno; lo ven su comité y el pastor, y nadie más', function () {
    $this->actingAs($this->e['presidente'])->post('/schedules', ['name' => 'Ensayos del semestre'] + $this->periodo)->assertSessionHasNoErrors();

    $cronograma = Schedule::withoutGlobalScopes()->where('name', 'Ensayos del semestre')->sole();
    expect($cronograma->only(['kind', 'committee_id', 'year']))->toBe(['kind' => 'internal', 'committee_id' => $this->e['alabanza']->id, 'year' => 2026]);

    foreach (['presidente', 'secretaria', 'tesorero', 'pastor'] as $quien) {
        $this->actingAs($this->e[$quien])->get('/schedules')->assertShows('Ensayos del semestre');
        $this->actingAs($this->e[$quien])->get("/schedules/{$cronograma->id}")->assertOk();
    }
    foreach (['liderSonido', 'presidentaDamas'] as $quien) {
        $this->actingAs($this->e[$quien])->get('/schedules')->assertDoesntShow('Ensayos del semestre');
        $this->actingAs($this->e[$quien])->get("/schedules/{$cronograma->id}")->assertNotFound();
    }
});

test('el líder lleva los internos de su sub-comité; su presidente los ve y la rama hermana no', function () {
    $this->actingAs($this->e['liderSonido'])->post('/schedules', ['name' => 'Mantenimiento de equipos'] + $this->periodo)->assertSessionHasNoErrors();

    $this->actingAs($this->e['presidente'])->get('/schedules')->assertShows('Mantenimiento de equipos');
    $this->actingAs($this->e['liderCoro'])->get('/schedules')->assertDoesntShow('Mantenimiento de equipos');
    expect(Schedule::withoutGlobalScopes()->where('name', 'Mantenimiento de equipos')->value('committee_id'))->toBe($this->e['sonido']->id);
});

test('escenario 2: la actividad con su tema, sus horas, su lugar y quién la dirige queda registrada sin aprobación', function () {
    $cronograma = ($this->ensayos)();

    $this->actingAs($this->e['presidente'])->post("/schedules/{$cronograma->id}/activities", $this->actividad)
        ->assertRedirect("/schedules/{$cronograma->id}")
        ->assertSessionHasNoErrors();

    expect(Activity::query()->where('title', 'Ensayo general')->sole())
        ->schedule_id->toBe($cronograma->id)
        ->committee_id->toBe($this->e['alabanza']->id)
        ->status->toBe(Activity::APPROVED)
        ->theme->toBe('Cantad alegres a Dios')
        ->responsible_name->toBe('La directora del coro')
        ->and(Approval::withoutGlobalScopes()->count())->toBe(0);

    $this->actingAs($this->e['secretaria'])->get("/schedules/{$cronograma->id}")
        ->assertInertia(fn (Assert $page) => $page
            ->component('Schedules/Show')
            ->where('activities.0.title', 'Ensayo general')
            ->where('activities.0.responsible', 'La directora del coro')
            ->where('activities.0.type', 'Ensayo')
            ->where('canAdd', false));
});

test('escenario 4: una actividad interna se cruza sin problema con otra interna y con una del general', function () {
    $cronograma = ($this->ensayos)();
    Activity::factory()->forCommittee($this->e['damas'])->approved()->create([
        'title' => 'Vigilia de damas',
        'scheduled_at' => Carbon::parse('2026-04-18 19:00', 'America/Bogota'),
        'ends_at' => Carbon::parse('2026-04-18 21:00', 'America/Bogota'),
    ]);

    $this->actingAs($this->e['presidente'])->post("/schedules/{$cronograma->id}/activities", $this->actividad)->assertSessionHasNoErrors();
    $this->actingAs($this->e['presidente'])->post("/schedules/{$cronograma->id}/activities", ['title' => 'Ensayo de voces', 'time' => '19:30', 'ends' => '20:30'] + $this->actividad)->assertSessionHasNoErrors();

    expect(Activity::query()->where('schedule_id', $cronograma->id)->count())->toBe(2);
});

test('escenario 5: otro comité no ve el interno por ninguna vía, ni sabe que existe', function () {
    $cronograma = ($this->ensayos)();
    $this->actingAs($this->e['presidente'])->post("/schedules/{$cronograma->id}/activities", $this->actividad);
    $ensayo = Activity::query()->where('title', 'Ensayo general')->sole();

    $this->actingAs($this->e['presidentaDamas'])->get('/schedules')
        ->assertInertia(fn (Assert $page) => $page->where('schedules', []));
    $this->actingAs($this->e['presidentaDamas'])->get("/schedules/{$cronograma->id}")->assertNotFound()->assertDoesntShow('Ensayos del semestre');
    $this->actingAs($this->e['presidentaDamas'])->post("/schedules/{$cronograma->id}/activities", $this->actividad)->assertNotFound();
    $this->actingAs($this->e['presidentaDamas'])->get("/activities/{$ensayo->id}")->assertNotFound();
    $this->actingAs($this->e['presidentaDamas'])->get('/activities?year=2026')->assertDoesntShow('Ensayo general');
    $this->actingAs($this->e['presidentaDamas'])->get('/activities?year=2026&month=4')->assertDoesntShow('Ensayo general');

    expect(Activity::withoutGlobalScopes()->where('schedule_id', $cronograma->id)->count())->toBe(1);
});

test('escenario 6: el gasto de una actividad interna sigue la cadena de aprobación de siempre', function () {
    $mantenimiento = Schedule::factory()->internalOf($this->e['sonido'], 'Mantenimiento de equipos')->create($this->periodo);
    $cables = Activity::factory()->forCommittee($this->e['sonido'])->approved()->create(['schedule_id' => $mantenimiento->id, 'title' => 'Cambio de cables']);
    $lider = CommitteeUser::where('user_id', $this->e['liderSonido']->id)->sole();

    $gasto = app(ApprovalFlow::class)->request($lider, $cables, Approval::TYPE_EXPENSE, 'Cables nuevos', null, 250000);

    expect($gasto->level)->toBe(Approval::LEVEL_PRESIDENT)
        ->and($gasto->status)->toBe(Approval::PENDING);
});

test('la secretaria, el tesorero y el pastor ven, pero no crean cronogramas ni les agregan actividades', function () {
    $cronograma = ($this->ensayos)();

    foreach (['secretaria', 'tesorero', 'pastor'] as $quien) {
        $this->actingAs($this->e[$quien])->get('/schedules')->assertInertia(fn (Assert $page) => $page->where('createFor', null));
        $this->actingAs($this->e[$quien])->post('/schedules', ['name' => 'Otro'] + $this->periodo)->assertForbidden();
        $this->actingAs($this->e[$quien])->post("/schedules/{$cronograma->id}/activities", $this->actividad)->assertForbidden();
    }

    $this->actingAs($this->e['presidente'])->get('/schedules')->assertInertia(fn (Assert $page) => $page->where('createFor', 'Comité de Alabanza'));
    expect(Schedule::withoutGlobalScopes()->where('kind', 'internal')->count())->toBe(1);
});

test('el presidente no agrega actividades al interno de su sub-comité: eso lo lleva su líder', function () {
    $mantenimiento = Schedule::factory()->internalOf($this->e['sonido'], 'Mantenimiento de equipos')->create($this->periodo);

    $this->actingAs($this->e['presidente'])->get("/schedules/{$mantenimiento->id}")->assertInertia(fn (Assert $page) => $page->where('canAdd', false));
    $this->actingAs($this->e['presidente'])->post("/schedules/{$mantenimiento->id}/activities", $this->actividad)->assertForbidden();
});

test('el cronograma general no se abre como interno', function () {
    $general = Schedule::generalOf($this->e['sede']->id, 2026);

    $this->actingAs($this->e['pastor'])->get("/schedules/{$general->id}")->assertNotFound();
    $this->actingAs($this->e['presidente'])->post("/schedules/{$general->id}/activities", $this->actividad)->assertForbidden();
});

test('cada dato del cronograma que falta o está mal se señala en su campo', function (array $cambio, string $campo, string $mensaje) {
    $this->actingAs($this->e['presidente'])->post('/schedules', $cambio + ['name' => 'Módulo 1'] + $this->periodo)
        ->assertSessionHasErrors([$campo => $mensaje]);
})->with([
    'sin nombre' => [['name' => ''], 'name', 'Escriba el nombre del cronograma. Por ejemplo: Módulo 1.'],
    'termina antes de empezar' => [['ends_on' => '2026-01-15'], 'ends_on', 'La fecha en que termina debe ser después de la fecha en que empieza.'],
    'de un año ya pasado' => [['starts_on' => '2025-02-01'], 'starts_on', 'Escoja una fecha de 2026 o de 2027.'],
    'de un año a otro' => [['ends_on' => '2027-01-31'], 'ends_on', 'El cronograma termina el mismo año en que empieza. Para el año siguiente, cree otro.'],
]);

test('cada dato de la actividad que falta o está mal se señala en su campo', function (array $cambio, string $campo, string $mensaje) {
    $cronograma = ($this->ensayos)();

    $this->actingAs($this->e['presidente'])->post("/schedules/{$cronograma->id}/activities", $cambio + $this->actividad)
        ->assertSessionHasErrors([$campo => $mensaje]);
})->with([
    'sin nombre' => [['title' => ''], 'title', 'Escriba el nombre de la actividad.'],
    'sin clase' => [['activity_type_id' => ''], 'activity_type_id', 'Escoja qué clase de actividad es.'],
    'antes del periodo' => [['date' => '2026-01-20'], 'date', 'La fecha debe estar dentro del cronograma: del 1 de febrero de 2026 al 30 de junio de 2026.'],
    'después del periodo' => [['date' => '2026-07-01'], 'date', 'La fecha debe estar dentro del cronograma: del 1 de febrero de 2026 al 30 de junio de 2026.'],
    'termina antes de empezar' => [['ends' => '18:00'], 'ends', 'La hora de fin debe ser después de la hora de inicio.'],
    'responsable muy largo' => [['responsible' => str_repeat('a', 81)], 'responsible', 'El nombre de quien la dirige es muy largo. Resúmalo en menos de 80 letras.'],
]);

test('la ficha del cronograma cabe en el presupuesto y no crece con sus actividades', function () {
    $cronograma = ($this->ensayos)();
    $medir = function () use ($cronograma) {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($this->e['presidente'])->get("/schedules/{$cronograma->id}")->assertOk();
        $consultas = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $consultas;
    };

    $antes = $medir();
    Activity::factory()->count(6)->forCommittee($this->e['alabanza'])->approved()->create(['schedule_id' => $cronograma->id]);

    expect($antes)->toBeLessThanOrEqual(5)
        ->and($medir())->toBe($antes);
});
