<?php

use App\Models\Activity;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

/* El cronograma del año (T080, HU-09 escenario 1). */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-06-15 10:00', 'America/Bogota'));
    $this->crear = fn ($comite, string $titulo, string $estado, string $fecha) => Activity::factory()->forCommittee($comite)->create([
        'title' => $titulo,
        'status' => $estado,
        'rejection_reason' => $estado === Activity::REJECTED ? 'No alcanza el presupuesto.' : null,
        'scheduled_at' => Carbon::parse($fecha, 'America/Bogota'),
    ]);
});

test('el año muestra los doce meses y distingue cumplidas, en camino, aprobadas sin empezar y esperando aprobación', function () {
    ($this->crear)($this->e['sonido'], 'Revisión de sonido', Activity::COMPLETED, '2026-03-10 19:00');
    ($this->crear)($this->e['sonido'], 'Capacitación de sonido', Activity::APPROVED, '2026-05-20 19:00');
    ($this->crear)($this->e['coro'], 'Cantata de Navidad', Activity::APPROVED, '2026-12-12 19:00');
    ($this->crear)($this->e['coro'], 'Montaje del templo', Activity::PENDING, '2026-07-01 19:00');
    ($this->crear)($this->e['sonido'], 'Paseo rechazado', Activity::REJECTED, '2026-08-01 19:00');

    $this->actingAs($this->e['presidente'])->get('/activities')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Activities/Index')
            ->where('filters.year', 2026)
            ->has('months', 12)
            ->where('summary.total', 4)
            ->where('summary.completed', 1)
            ->where('summary.inProgress', 1)
            ->where('summary.approved', 1)
            ->where('summary.pending', 1)
            ->where('summary.compliance', 50)
            ->where('months.2.activities.0.title', 'Revisión de sonido')
            ->where('months.4.activities.0.status', Activity::IN_PROGRESS)
            ->where('canPropose', true))
        ->assertDoesntShow('Paseo rechazado');
});

test('un líder ve lo de su comité completo y lo de los demás con lo que se comparte', function () {
    ($this->crear)($this->e['sonido'], 'Revisión de sonido', Activity::APPROVED, '2026-09-10 19:00');
    ($this->crear)($this->e['coro'], 'Cantata de Navidad', Activity::APPROVED, '2026-09-12 19:00');

    $this->actingAs($this->e['liderSonido'])->get('/activities')
        ->assertInertia(fn (Assert $page) => $page
            ->where('months.8.activities.0.title', 'Revisión de sonido')
            ->has('months.8.activities.0.progress')
            ->where('months.8.activities.1.title', 'Cantata de Navidad')
            ->missing('months.8.activities.1.progress'));
});

test('el pastor ve toda la congregación como propia', function () {
    ($this->crear)($this->e['damas'], 'Retiro de damas', Activity::APPROVED, '2026-09-10 19:00');

    $this->actingAs($this->e['pastor'])->get('/activities')
        ->assertInertia(fn (Assert $page) => $page->has('months.8.activities.0.progress')->where('canPropose', false));
});
