<?php

use App\Models\Member;
use Inertia\Testing\AssertableInertia as Assert;

/* Los casos límite de la ficha de miembro (T097; CL-02, CL-03, CL-16). */

beforeEach(function () {
    $this->e = estructuraDePrueba();
});

test('quien no está en ningún comité cuenta para el pastor y se señala como oportunidad (CL-03)', function () {
    Member::factory()->create(['site_id' => $this->e['sede']->id, 'committee_id' => null, 'name' => 'Hermana Sin Comité']);

    $this->actingAs($this->e['pastor'])->get('/members')
        ->assertInertia(fn (Assert $page) => $page->where('totalCount', 1)->where('members.0.committee', null));

    $this->actingAs($this->e['presidente'])->get('/members')->assertDoesntShow('Hermana Sin Comité');
});

test('quien está en el comité pero en ningún sub-comité también aparece (CL-02)', function () {
    Member::factory()->forCommittee($this->e['alabanza'])->create(['name' => 'Hermano de Alabanza']);

    $this->actingAs($this->e['presidente'])->get('/members')->assertShows('Hermano de Alabanza');
    $this->actingAs($this->e['liderSonido'])->get('/members')->assertDoesntShow('Hermano de Alabanza');
});

test('la ficha sin fecha ni edad queda por completar, y se cuenta (CL-16)', function () {
    Member::factory()->forCommittee($this->e['sonido'])->create(['birth_date' => null, 'profile_pending' => true]);
    Member::factory()->forCommittee($this->e['sonido'])->create();

    $this->actingAs($this->e['liderSonido'])->get('/members')
        ->assertInertia(fn (Assert $page) => $page->where('pendingProfiles', 1));
});

test('una ficha nunca se borra', function () {
    Member::factory()->forCommittee($this->e['sonido'])->create()->delete();
})->throws(RuntimeException::class, 'no se borra');
