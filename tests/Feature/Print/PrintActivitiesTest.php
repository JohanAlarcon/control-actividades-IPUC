<?php

use App\Models\Activity;
use App\Models\Committee;
use App\Models\Schedule;
use Illuminate\Support\Carbon;

/*
 * Imprimir y compartir lo de un comité (T228; HU-30, RF-089): sus actividades aprobadas y las de sus
 * sub-comités, del cronograma general y de los internos, en unas fechas, con su logo y su lema
 * (HU-26 escenario 2). Un líder solo tiene lo de su sub-comité, y lo ajeno no existe.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-03-10 10:00', 'America/Bogota'));

    $this->actividad = fn (Committee $comite, string $titulo, string $cuando, array $mas = []) => Activity::factory()->forCommittee($comite)->approved()->create([
        'title' => $titulo,
        'scheduled_at' => Carbon::parse($cuando, 'America/Bogota'),
    ] + $mas);
    $this->interna = fn (Committee $comite) => Schedule::factory()->internalOf($comite, 'Primer semestre')->create(['starts_on' => '2026-02-01', 'ends_on' => '2026-06-30']);
    $this->props = fn ($respuesta) => $respuesta->assertOk()->viewData('page')['props'];
});

test('escenario 1: el presidente imprime lo del comité y de sus sub-comités, general e interno, con lugar y responsable', function () {
    $interno = ($this->interna)($this->e['coro'])->id;
    ($this->actividad)($this->e['alabanza'], 'Culto de alabanza', '2026-04-12 18:00', ['location' => 'Templo central']);
    ($this->actividad)($this->e['sonido'], 'Revisión de equipos', '2026-04-05 09:00');
    ($this->actividad)($this->e['coro'], 'Ensayo del coro', '2026-04-18 19:00', ['schedule_id' => $interno, 'responsible_name' => 'La directora del coro']);
    ($this->actividad)($this->e['alabanza'], 'Propuesta sin aprobar', '2026-04-20 18:00', ['status' => Activity::PENDING, 'approved_at' => null]);
    ($this->actividad)($this->e['alabanza'], 'No aprobada', '2026-04-21 18:00', ['status' => Activity::REJECTED]);
    ($this->actividad)($this->e['coro'], 'Ensayo cancelado', '2026-04-25 19:00', ['schedule_id' => $interno, 'cancelled_at' => now()]);
    ($this->actividad)($this->e['alabanza'], 'Culto de mayo', '2026-05-03 18:00');
    ($this->actividad)($this->e['damas'], 'Vigilia de damas', '2026-04-10 20:00');

    $props = ($this->props)($this->actingAs($this->e['presidente'])->get("/print/activities?committee={$this->e['alabanza']->id}&from=2026-04-01&to=2026-04-30"));

    expect(array_column($props['activities'], 'title'))->toBe(['Revisión de equipos', 'Culto de alabanza', 'Ensayo del coro'])
        ->and($props['period'])->toBe(['from' => '2026-04-01', 'to' => '2026-04-30'])
        ->and($props['activities'][1])->toMatchArray(['location' => 'Templo central', 'committee' => 'Comité de Alabanza', 'responsible' => null])
        ->and($props['activities'][2])->toMatchArray(['committee' => 'Sub-comité de Coro', 'responsible' => 'La directora del coro']);
});

test('HU-26 escenario 2: la hoja lleva el logo y el lema del comité', function () {
    $this->e['alabanza']->update(['motto' => 'Cantad alegres a Dios', 'logo_path' => 'committees/1/logo.png', 'identity_updated_at' => now()]);

    $props = ($this->props)($this->actingAs($this->e['secretaria'])->get('/print/activities'));

    expect($props['committee'])->toBe([
        'id' => $this->e['alabanza']->id,
        'name' => 'Comité de Alabanza',
        'motto' => 'Cantad alegres a Dios',
        'logoUrl' => "/committees/{$this->e['alabanza']->id}/logo?v=".now()->timestamp,
    ]);
});

test('escenario 3: el líder solo tiene lo de su sub-comité; el comité de arriba y la rama hermana no existen para él', function () {
    ($this->actividad)($this->e['sonido'], 'Revisión de equipos', '2026-04-05 09:00');
    ($this->actividad)($this->e['coro'], 'Ensayo del coro', '2026-04-18 19:00');
    ($this->actividad)($this->e['alabanza'], 'Culto de alabanza', '2026-04-12 18:00');

    $props = ($this->props)($this->actingAs($this->e['liderSonido'])->get('/print/activities?from=2026-04-01&to=2026-04-30'));

    expect($props['committee']['name'])->toBe('Sub-comité de Sonido')
        ->and(array_column($props['activities'], 'title'))->toBe(['Revisión de equipos']);

    $this->actingAs($this->e['liderSonido'])->get("/print/activities?committee={$this->e['alabanza']->id}")->assertNotFound();
    $this->actingAs($this->e['liderSonido'])->get("/print/activities?committee={$this->e['coro']->id}")->assertNotFound();
});

test('un cargo ajeno no la ve, y el pastor imprime la de cualquier comité', function () {
    ($this->actividad)($this->e['alabanza'], 'Culto de alabanza', '2026-04-12 18:00');

    $this->actingAs($this->e['presidentaDamas'])->get("/print/activities?committee={$this->e['alabanza']->id}")->assertNotFound();

    $props = ($this->props)($this->actingAs($this->e['pastor'])->get("/print/activities?committee={$this->e['alabanza']->id}&from=2026-04-01&to=2026-04-30"));
    expect(array_column($props['activities'], 'title'))->toBe(['Culto de alabanza']);
});

test('sin fechas, desde hoy hasta dentro de un mes; las fechas que no sirven se ignoran y el final nunca queda antes del comienzo', function () {
    $periodo = fn (string $consulta) => ($this->props)($this->actingAs($this->e['presidente'])->get("/print/activities{$consulta}"))['period'];

    expect($periodo(''))->toBe(['from' => '2026-03-10', 'to' => '2026-04-10'])
        ->and($periodo('?from=2026-02-31&to=mañana'))->toBe(['from' => '2026-03-10', 'to' => '2026-04-10'])
        ->and($periodo('?from=2026-04-20&to=2026-04-01'))->toBe(['from' => '2026-04-20', 'to' => '2026-04-20'])
        ->and($periodo('?from=2026-01-01&to=2030-01-01'))->toBe(['from' => '2026-01-01', 'to' => '2027-01-02']);
});
