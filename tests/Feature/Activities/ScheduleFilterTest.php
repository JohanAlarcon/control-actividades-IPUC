<?php

use App\Models\Activity;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Los filtros del cronograma (T081, RF-029, RF-030, HU-09 escenario 2): al escoger un comite, la
 * lista, los totales y el cumplimiento se recalculan solo con el y con lo que cuelga de el.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-06-15 10:00', 'America/Bogota'));

    $crear = fn ($comite, string $titulo, string $estado, string $fecha) => Activity::factory()->forCommittee($comite)->create([
        'title' => $titulo,
        'status' => $estado,
        'scheduled_at' => Carbon::parse($fecha, 'America/Bogota'),
    ]);

    $crear($this->e['sonido'], 'Sonido uno', Activity::COMPLETED, '2026-02-10 19:00');
    $crear($this->e['sonido'], 'Sonido dos', Activity::APPROVED, '2026-03-10 19:00');
    $crear($this->e['montaje'], 'Montaje uno', Activity::COMPLETED, '2026-04-10 19:00');
    $crear($this->e['coro'], 'Coro uno', Activity::COMPLETED, '2026-02-15 19:00');
    $crear($this->e['coro'], 'Coro dos', Activity::COMPLETED, '2026-03-15 19:00');
});

test('al escoger un comité, la lista, el resumen y el cumplimiento se recalculan con él y lo que cuelga de él', function () {
    $this->actingAs($this->e['presidente'])->get('/activities')
        ->assertInertia(fn (Assert $page) => $page->where('summary.total', 5)->where('summary.compliance', 80));

    $this->actingAs($this->e['presidente'])->get("/activities?committee={$this->e['sonido']->id}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.committee', $this->e['sonido']->id)
            ->where('summary.total', 3)
            ->where('summary.compliance', 67))
        ->assertShows('Montaje uno')
        ->assertDoesntShow('Coro uno');

    $this->actingAs($this->e['presidente'])->get("/activities?committee={$this->e['coro']->id}")
        ->assertInertia(fn (Assert $page) => $page->where('summary.total', 2)->where('summary.compliance', 100));
});

test('se filtra por mes y por estado, y los filtros quedan en la dirección', function () {
    $this->actingAs($this->e['presidente'])->get('/activities?month=2')
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.month', 2)
            ->has('months', 1)
            ->where('months.0.month', 2)
            ->has('months.0.activities', 2));

    $this->actingAs($this->e['presidente'])->get('/activities?status=in_progress')
        ->assertInertia(fn (Assert $page) => $page->where('summary.total', 1)->where('filters.status', 'in_progress'))
        ->assertShows('Sonido dos');
});

test('un comité que no es suyo no se puede filtrar para ver lo de adentro', function () {
    $this->actingAs($this->e['liderSonido'])->get("/activities?committee={$this->e['coro']->id}")
        ->assertInertia(fn (Assert $page) => $page->where('summary.total', 0))
        ->assertDoesntShow('Coro uno');
});
