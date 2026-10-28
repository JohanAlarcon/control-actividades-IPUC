<?php

use App\Models\Activity;
use App\Models\Member;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * La excepción 2 del principio I (T216, constitución 3.0.0, D-34): BIS ve a todos los simpatizantes
 * de la sede y, para asignarlos, el número y el nombre de los comités activos. Nada más: ni
 * miembros, ni actividades, ni datos de los comités. Es una lista blanca.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->ev = evangelismoDePrueba($this->e);
    $this->bis = bisDePrueba($this->e);
    simpatizanteDePrueba($this->ev, ['name' => 'Andrea Milena Castro']);
    $this->deDamas = simpatizanteDePrueba($this->ev, ['name' => 'Liliana Vargas']);
    $this->deDamas->update(['committee_id' => $this->e['damas']->id]);
});

test('BIS ve a todos los simpatizantes, de cualquier comité', function () {
    $this->actingAs($this->bis['presidentaBis'])->get('/sympathizers')
        ->assertInertia(fn (Assert $page) => $page->where('isReception', true)->has('list', 2));

    $this->actingAs($this->bis['presidentaBis'])->get("/sympathizers/{$this->deDamas->id}")->assertOk();
});

test('un comité ve solo los asignados a su ámbito', function () {
    $this->actingAs($this->e['presidentaDamas'])->get('/sympathizers')
        ->assertInertia(fn (Assert $page) => $page->where('isReception', false)->has('list', 1)->where('list.0.name', 'Liliana Vargas')->where('byCommittee', null));
});

test('BIS no ve por esto ningún miembro ni ninguna actividad ajena', function () {
    Member::factory()->forCommittee($this->e['damas'])->create(['name' => 'Hermana de Damas']);
    $actividad = Activity::factory()->forCommittee($this->e['damas'])->create(['title' => 'Retiro de damas', 'description' => 'Reservado de Damas.']);

    $this->actingAs($this->bis['presidentaBis'])->get('/members')->assertDoesntShow('Hermana de Damas');
    $this->actingAs($this->bis['presidentaBis'])->get("/activities/{$actividad->id}")->assertNotFound();
});

test('la lista para asignar trae exactamente el número y el nombre de los comités activos', function () {
    $this->e['coro']->update(['active' => false]);

    $this->actingAs($this->bis['presidentaBis'])->get('/sympathizers/create')
        ->assertInertia(fn (Assert $page) => $page->where('committees', function ($comites) {
            $comites = collect($comites);

            expect($comites->every(fn ($comite) => array_keys((array) $comite) === ['id', 'name']))->toBeTrue()
                ->and($comites->pluck('name'))->toContain('Comité de Damas Dorcas', 'Comité BIS')
                ->and($comites->pluck('name'))->not->toContain('Sub-comité de Coro');

            return true;
        }));
});
