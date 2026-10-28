<?php

use App\Models\Activity;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * El cronograma para imprimir con el formato de la sede (T256; RF-117, HU-09 escenario 7). Cada
 * quien imprime lo que ve: lo ajeno sale con los seis datos que se comparten, y el pastor, que ve
 * todo, imprime el cronograma completo con sus colores. El presupuesto lo mide QueryBudgetTest.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-03-10 10:00', 'America/Bogota'));

    $this->retiro = Activity::factory()->forCommittee($this->e['damas'])->create([
        'title' => 'Canasta de amor',
        'theme' => 'Dad, y se os dará',
        'highlight' => 'special_day',
        'description' => 'Recogemos mercado para las familias.',
        'budget_amount' => 987654,
        'status' => Activity::APPROVED,
        'scheduled_at' => Carbon::parse('2026-05-24 09:45', 'America/Bogota'),
    ]);
    Activity::factory()->forCommittee($this->e['sonido'])->create([
        'title' => 'Ensayo de alabanza',
        'status' => Activity::APPROVED,
        'scheduled_at' => Carbon::parse('2026-05-18 18:00', 'America/Bogota'),
    ]);
});

test('sin mes, sale el año completo: una hoja por mes', function () {
    $this->actingAs($this->e['presidente'])->get('/activities/print?year=2026')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Activities/Print')
            ->where('filters.year', 2026)
            ->where('filters.month', null)
            ->has('months', 12));
});

test('con mes, solo ese mes', function () {
    $this->actingAs($this->e['presidente'])->get('/activities/print?year=2026&month=5')
        ->assertInertia(fn (Assert $page) => $page
            ->has('months', 1)
            ->where('months.0.month', 5)
            ->has('months.0.activities', 2));
});

test('un presidente imprime lo ajeno con los seis datos, sin lo reservado', function () {
    $this->actingAs($this->e['presidente'])->get('/activities/print?year=2026&month=5')
        ->assertInertia(fn (Assert $page) => $page->where('months.0.activities', function ($actividades) {
            $ajena = collect($actividades)->firstWhere('title', 'Canasta de amor');
            $llaves = array_keys($ajena);
            sort($llaves);

            expect($llaves)->toBe(['committee', 'endsAt', 'id', 'location', 'scheduledAt', 'status', 'title']);

            return true;
        }))
        ->assertDoesntShow('987654')
        ->assertDoesntShow('Recogemos mercado')
        ->assertDoesntShow('Dad, y se os dará');
});

test('el pastor imprime el cronograma completo, con los temas y las marcas de todos', function () {
    $this->actingAs($this->e['pastor'])->get('/activities/print?year=2026&month=5')
        ->assertInertia(fn (Assert $page) => $page
            ->where('months.0.activities.1.title', 'Canasta de amor')
            ->where('months.0.activities.1.theme', 'Dad, y se os dará')
            ->where('months.0.activities.1.highlight', 'special_day'));
});

test('los filtros de la dirección se respetan: el comité escogido y nada más', function () {
    $this->actingAs($this->e['pastor'])->get("/activities/print?year=2026&month=5&committee={$this->e['damas']->id}")
        ->assertInertia(fn (Assert $page) => $page->has('months.0.activities', 1)->where('months.0.activities.0.title', 'Canasta de amor'));
});

test('sin sesión no se imprime nada', function () {
    $this->get('/activities/print?year=2026')->assertRedirect('/login');
});
