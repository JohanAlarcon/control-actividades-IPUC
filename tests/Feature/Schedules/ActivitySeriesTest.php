<?php

use App\Models\Activity;
use App\Models\ActivitySeries;
use App\Models\ActivityType;
use App\Models\Schedule;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Lo que se repite en un cronograma interno (T226; HU-27 escenario 3, D-38, CL-30). Cada fecha
 * queda como una actividad suelta de la misma serie, y se mueve o se cancela sola. Cancelar no
 * borra: deja quién y cuándo (principio III).
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-03-10 10:00', 'America/Bogota'));
    $this->ensayos = Schedule::factory()->internalOf($this->e['alabanza'], 'Ensayos del semestre')->create(['starts_on' => '2026-02-01', 'ends_on' => '2026-06-30']);
    $this->actividad = [
        'title' => 'Ensayo general',
        'activity_type_id' => ActivityType::where('site_id', $this->e['sede']->id)->where('name', 'Ensayo')->value('id'),
        'date' => '2026-04-04',
        'time' => '19:00',
        'ends' => '21:00',
        'repeat' => 'weekly',
        'until' => '2026-04-25',
    ];
    $this->serie = function () {
        $this->actingAs($this->e['presidente'])->post("/schedules/{$this->ensayos->id}/activities", $this->actividad)->assertSessionHasNoErrors();

        return Activity::query()->where('schedule_id', $this->ensayos->id)->orderBy('scheduled_at')->get();
    };
    $this->dias = fn () => Activity::query()->where('schedule_id', $this->ensayos->id)->orderBy('scheduled_at')->get()
        ->map(fn (Activity $fecha) => $fecha->scheduled_at->setTimezone('America/Bogota')->format('Y-m-d H:i'))->all();
});

test('escenario 3: lo que se repite cada semana queda como una actividad por fecha, de la misma serie', function () {
    $this->actingAs($this->e['presidente'])->post("/schedules/{$this->ensayos->id}/activities", $this->actividad)
        ->assertSessionHas('notice', 'Listo. «Ensayo general» quedó en el cronograma en 4 fechas. Las actividades internas no pasan por aprobación.');

    $fechas = Activity::query()->where('schedule_id', $this->ensayos->id)->get();
    expect(($this->dias)())->toBe(['2026-04-04 19:00', '2026-04-11 19:00', '2026-04-18 19:00', '2026-04-25 19:00'])
        ->and($fechas->pluck('series_id')->unique()->all())->toBe([ActivitySeries::query()->sole()->id])
        ->and(ActivitySeries::query()->sole()->only(['frequency', 'schedule_id']))->toBe(['frequency' => 'weekly', 'schedule_id' => $this->ensayos->id]);
});

test('cada mes, el 31 cae el último día de los meses cortos', function () {
    $this->actingAs($this->e['presidente'])->post("/schedules/{$this->ensayos->id}/activities", ['date' => '2026-03-31', 'repeat' => 'monthly', 'until' => '2026-06-30'] + $this->actividad)->assertSessionHasNoErrors();

    expect(($this->dias)())->toBe(['2026-03-31 19:00', '2026-04-30 19:00', '2026-05-31 19:00', '2026-06-30 19:00']);
});

test('lo que no se repite queda en una sola fecha, sin serie', function () {
    $this->actingAs($this->e['presidente'])->post("/schedules/{$this->ensayos->id}/activities", ['repeat' => '', 'until' => ''] + $this->actividad)->assertSessionHasNoErrors();

    expect(Activity::query()->where('schedule_id', $this->ensayos->id)->sole()->series_id)->toBeNull()
        ->and(ActivitySeries::query()->count())->toBe(0);
});

test('cada dato de la repetición que falta o está mal se señala en su campo', function (array $cambio, string $campo, string $mensaje) {
    $this->actingAs($this->e['presidente'])->post("/schedules/{$this->ensayos->id}/activities", $cambio + $this->actividad)
        ->assertSessionHasErrors([$campo => $mensaje]);

    expect(Activity::query()->where('schedule_id', $this->ensayos->id)->count())->toBe(0);
})->with([
    'sin hasta cuándo' => [['until' => ''], 'until', 'Escoja hasta qué fecha se repite.'],
    'una frecuencia que no existe' => [['repeat' => 'daily'], 'repeat', 'Escoja cada cuánto se repite.'],
    'la última antes de la primera' => [['until' => '2026-04-01'], 'until', 'La última fecha debe ser después de la primera.'],
    'fuera del cronograma' => [['until' => '2026-07-15'], 'until', 'La última fecha debe estar dentro del cronograma: hasta el 30 de junio de 2026.'],
]);

test('más de sesenta fechas no se crean', function () {
    $largo = Schedule::factory()->internalOf($this->e['alabanza'], 'Escuela de música')->create(['starts_on' => '2026-01-01', 'ends_on' => '2027-12-31']);

    $this->actingAs($this->e['presidente'])->post("/schedules/{$largo->id}/activities", ['until' => '2027-12-31'] + $this->actividad)
        ->assertSessionHasErrors(['until' => 'Son más de 60 fechas. Acorte el periodo o escoja que se repita más espaciado.']);

    expect(Activity::query()->where('schedule_id', $largo->id)->count())->toBe(0);
});

test('CL-30: cancelar una fecha deja las demás como estaban, y queda quién y cuándo', function () {
    $fechas = ($this->serie)();
    $this->travelTo(Carbon::parse('2026-03-12 08:00', 'America/Bogota'));

    $this->actingAs($this->e['presidente'])->post("/activities/{$fechas[1]->id}/cancel")
        ->assertSessionHas('notice', 'Listo. «Ensayo general» del sábado 11 de abril quedó cancelada. Las demás fechas siguen igual.');

    $despues = Activity::query()->where('schedule_id', $this->ensayos->id)->orderBy('scheduled_at')->get();
    expect($despues->pluck('id')->all())->toBe($fechas->pluck('id')->all())
        ->and($despues[1]->cancelled_by_user_id)->toBe($this->e['presidente']->id)
        ->and($despues[1]->cancelled_at->setTimezone('America/Bogota')->format('Y-m-d H:i'))->toBe('2026-03-12 08:00')
        ->and($despues->whereNotNull('cancelled_at')->count())->toBe(1);

    $this->actingAs($this->e['presidente'])->get("/schedules/{$this->ensayos->id}")
        ->assertInertia(fn (Assert $page) => $page->where('activities', fn ($lista) => collect($lista)->pluck('cancelled')->all() === [false, true, false, false]));
});

test('CL-30: mover una fecha deja las demás como estaban', function () {
    $fechas = ($this->serie)();

    $this->actingAs($this->e['presidente'])->post("/activities/{$fechas[2]->id}/move", ['date' => '2026-04-19', 'time' => '18:00', 'ends' => '20:00'])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('notice', 'Listo. «Ensayo general» quedó para el domingo 19 de abril. Las demás fechas siguen igual.');

    expect(($this->dias)())->toBe(['2026-04-04 19:00', '2026-04-11 19:00', '2026-04-19 18:00', '2026-04-25 19:00']);
});

test('una fecha no se mueve fuera del cronograma, ni con la hora de fin antes de la de inicio', function () {
    $fechas = ($this->serie)();

    $this->actingAs($this->e['presidente'])->post("/activities/{$fechas[0]->id}/move", ['date' => '2026-07-04', 'time' => '19:00', 'ends' => '21:00'])
        ->assertSessionHasErrors(['date' => 'La fecha debe estar dentro del cronograma: del 1 de febrero de 2026 al 30 de junio de 2026.']);
    $this->actingAs($this->e['presidente'])->post("/activities/{$fechas[0]->id}/move", ['date' => '2026-04-05', 'time' => '19:00', 'ends' => '18:00'])
        ->assertSessionHasErrors(['ends' => 'La hora de fin debe ser después de la hora de inicio.']);

    expect(($this->dias)()[0])->toBe('2026-04-04 19:00');
});

test('solo quien lleva el cronograma mueve o cancela; otro comité ni encuentra la fecha', function () {
    $fecha = ($this->serie)()->first();

    foreach (['secretaria', 'tesorero', 'pastor'] as $quien) {
        $this->actingAs($this->e[$quien])->post("/activities/{$fecha->id}/cancel")->assertForbidden();
        $this->actingAs($this->e[$quien])->post("/activities/{$fecha->id}/move", ['date' => '2026-04-05', 'time' => '19:00', 'ends' => '21:00'])->assertForbidden();
    }
    foreach (['presidentaDamas', 'liderSonido'] as $quien) {
        $this->actingAs($this->e[$quien])->post("/activities/{$fecha->id}/cancel")->assertNotFound();
    }

    expect(Activity::withoutGlobalScopes()->find($fecha->id))
        ->cancelled_at->toBeNull()
        ->scheduled_at->toDateTimeString()->toBe($fecha->scheduled_at->toDateTimeString());
});

test('lo del cronograma general no se mueve ni se cancela por aquí', function () {
    $vigilia = Activity::factory()->forCommittee($this->e['alabanza'])->approved()->create(['title' => 'Vigilia de jóvenes']);

    $this->actingAs($this->e['presidente'])->post("/activities/{$vigilia->id}/cancel")->assertForbidden();
    $this->actingAs($this->e['presidente'])->post("/activities/{$vigilia->id}/move", ['date' => '2026-04-05', 'time' => '19:00', 'ends' => '21:00'])->assertForbidden();

    expect($vigilia->fresh()->cancelled_at)->toBeNull();
});

test('una fecha cancelada ya no se mueve, y no sale entre las próximas del inicio', function () {
    $fechas = ($this->serie)();
    $this->actingAs($this->e['presidente'])->post("/activities/{$fechas[0]->id}/cancel");

    $this->actingAs($this->e['presidente'])->post("/activities/{$fechas[0]->id}/move", ['date' => '2026-04-05', 'time' => '19:00', 'ends' => '21:00'])->assertForbidden();

    $proximas = $this->actingAs($this->e['presidente'])->withHeaders(pedidoParcial('upcoming'))->get('/dashboard')->json('props.upcoming');
    expect(collect($proximas)->pluck('id')->all())->toBe($fechas->slice(1)->pluck('id')->values()->all());
});
