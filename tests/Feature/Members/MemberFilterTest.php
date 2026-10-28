<?php

use App\Models\Member;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

/* Los filtros combinables, con «cuántos cumplen sobre el total» (T095; HU-10 escenario 5; RF-035). */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-06-15 10:00', 'America/Bogota'));

    Member::factory()->forCommittee($this->e['sonido'])->create(['name' => 'Hermano Sonido', 'gender' => 'm', 'marital_status' => 'married', 'baptized' => true, 'baptized_at' => '2010-01-01', 'birth_date' => '1996-01-01']);
    Member::factory()->forCommittee($this->e['coro'])->create(['name' => 'Hermana Coro', 'gender' => 'f', 'marital_status' => 'single', 'birth_date' => null, 'age_reported' => 15, 'age_reported_at' => '2026-01-10']);
    Member::factory()->forCommittee($this->e['alabanza'])->create(['name' => 'Hermano Montaje', 'gender' => 'm', 'marital_status' => 'widowed', 'baptized' => true, 'baptized_at' => '1980-01-01', 'birth_date' => '1956-01-01']);
    Member::factory()->forCommittee($this->e['damas'])->create(['name' => 'Hermana Damas', 'gender' => 'f']);
});

test('5. los filtros se combinan y dicen cuántos cumplen sobre el total', function () {
    $this->actingAs($this->e['presidente'])->get('/members?gender=m&baptized=yes')
        ->assertInertia(fn (Assert $page) => $page
            ->where('totalCount', 3)
            ->has('members', 2)
            ->where('filters.gender', 'm')
            ->where('filters.baptized', 'yes'));

    $this->actingAs($this->e['presidente'])->get('/members?gender=m&marital=widowed')
        ->assertInertia(fn (Assert $page) => $page->has('members', 1)->where('members.0.name', 'Hermano Montaje'));
});

test('por comité incluye lo que cuelga de él', function () {
    $this->actingAs($this->e['presidente'])->get("/members?committee={$this->e['alabanza']->id}")
        ->assertInertia(fn (Assert $page) => $page->has('members', 3));

    $this->actingAs($this->e['presidente'])->get("/members?committee={$this->e['sonido']->id}")
        ->assertInertia(fn (Assert $page) => $page->has('members', 1))
        ->assertDoesntShow('Hermano Montaje')
        ->assertDoesntShow('Hermana Coro');
});

test('por rango de edad cuenta las dos fuentes: la fecha y la edad anotada', function () {
    $this->actingAs($this->e['presidente'])->get('/members?age=teens')
        ->assertInertia(fn (Assert $page) => $page->has('members', 1)->where('members.0.name', 'Hermana Coro'));

    $this->actingAs($this->e['presidente'])->get('/members?age=seniors')
        ->assertInertia(fn (Assert $page) => $page->has('members', 1)->where('members.0.name', 'Hermano Montaje'));
});

test('el total respeta el cargo activo: el líder cuenta solo lo de su sub-comité', function () {
    $this->actingAs($this->e['liderSonido'])->get('/members')
        ->assertInertia(fn (Assert $page) => $page->where('totalCount', 1))
        ->assertDoesntShow('Hermana Damas');
});
