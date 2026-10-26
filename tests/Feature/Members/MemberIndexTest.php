<?php

use App\Models\Member;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

/* La lista y la ficha de miembros (T096, T098; HU-10 escenarios 1 y 7, y el aislamiento). */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-06-15 10:00', 'America/Bogota'));
    $this->ficha = [
        'name' => 'Hermano Julián Restrepo',
        'age_mode' => 'date',
        'birth_date' => '1980-04-12',
        'gender' => 'm',
        'marital_status' => 'married',
        'committee_id' => $this->e['sonido']->id,
    ];
});

test('1. queda registrado, el sistema dice los años que tiene y aparece de una vez en la lista', function () {
    $this->actingAs($this->e['liderSonido'])->post('/members', $this->ficha)
        ->assertRedirect('/members')
        ->assertSessionHas('notice', 'Listo. Hermano Julián Restrepo quedó registrado. Tiene 46 años.');

    $this->actingAs($this->e['liderSonido'])->get('/members')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Members/Index')
            ->where('totalCount', 1)
            ->where('members.0.name', 'Hermano Julián Restrepo')
            ->where('members.0.age', 46)
            ->where('members.0.committee', 'Sub-comité de Sonido'));
});

test('4. la lista trae el nombre y el barrio de cada uno, para buscar en el teléfono sin volver a pedirla', function () {
    Member::factory()->forCommittee($this->e['sonido'])->create(['name' => 'Hermano Julián Hernández', 'neighborhood' => 'Picaleña']);

    $this->actingAs($this->e['liderSonido'])->get('/members')
        ->assertInertia(fn (Assert $page) => $page->where('members.0.name', 'Hermano Julián Hernández')->where('members.0.neighborhood', 'Picaleña'));
});

test('7. sin la fecha, con la edad anotada: se guarda con el día y sigue contando por edad', function () {
    $this->actingAs($this->e['liderSonido'])->post('/members', ['age_mode' => 'age', 'age_reported' => 40, 'birth_date' => '1980-04-12'] + $this->ficha)
        ->assertSessionHas('notice', 'Listo. Hermano Julián Restrepo quedó registrado. Tiene 40 años.');

    expect(Member::sole())->birth_date->toBeNull()->age_reported->toBe(40)->age_reported_at->toDateString()->toBe('2026-06-15');

    $this->travelTo(Carbon::parse('2028-07-01 10:00', 'America/Bogota'));

    $this->actingAs($this->e['liderSonido'])->get('/members?age=adults')
        ->assertInertia(fn (Assert $page) => $page->where('members.0.age', 42));
});

test('al corregir la ficha, si la edad no cambió, se conserva el día en que se anotó', function () {
    $miembro = Member::factory()->forCommittee($this->e['sonido'])->create(['birth_date' => null, 'age_reported' => 40, 'age_reported_at' => '2025-01-10']);

    $this->actingAs($this->e['liderSonido'])
        ->put("/members/{$miembro->id}", ['age_mode' => 'age', 'age_reported' => 40, 'name' => 'Hermano Corregido'] + $this->ficha)
        ->assertSessionHas('notice', 'Listo. Los datos de Hermano Corregido quedaron guardados.');

    expect($miembro->fresh())->name->toBe('Hermano Corregido')->age_reported_at->toDateString()->toBe('2025-01-10');
});

test('un cargo ajeno no ve ni abre los miembros de otro comité', function () {
    $miembro = Member::factory()->forCommittee($this->e['sonido'])->create(['name' => 'Hermano de Sonido']);

    $this->actingAs($this->e['presidentaDamas'])->get('/members')->assertOk()->assertDoesntShow('Hermano de Sonido');
    $this->actingAs($this->e['presidentaDamas'])->get("/members/{$miembro->id}/edit")->assertNotFound();
    $this->actingAs($this->e['presidentaDamas'])->put("/members/{$miembro->id}", $this->ficha)->assertNotFound();

    $ocultos = $this->actingAs($this->e['presidentaDamas'])->withHeaders(pedidoParcial('scopeHidden', 'Members/Index'))->get('/members');
    expect($ocultos->json('props.scopeHidden.members'))->toBe(1);
});

it('la ficha para corregir cabe en el presupuesto y no crece con los datos', function () {
    $miembro = Member::factory()->forCommittee($this->e['sonido'])->create();
    $medir = function () use ($miembro) {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($this->e['liderSonido'])->get("/members/{$miembro->id}/edit")->assertOk();
        $consultas = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $consultas;
    };

    $antes = $medir();
    Member::factory()->forCommittee($this->e['sonido'])->count(8)->create();

    expect($antes)->toBeLessThanOrEqual(5)->and($medir())->toBe($antes);
});
