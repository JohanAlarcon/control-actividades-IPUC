<?php

use App\Models\CommitteeMember;
use App\Models\Member;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Los miembros entran al ámbito por sus pertenencias (T213; D-32, RF-014, CL-03, CL-27): un
 * miembro de dos comités lo ven los dos y nadie más, se cuenta una sola vez, y el pastor ve también
 * a quien todavía no está en ningún comité. Ninguna de estas pruebas filtra a mano.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-06-15 10:00', 'America/Bogota'));
    $this->doble = Member::factory()->forCommittee($this->e['sonido'])->create(['name' => 'Hermana de Dos Comités']);
    CommitteeMember::create(['site_id' => $this->e['sede']->id, 'member_id' => $this->doble->id, 'committee_id' => $this->e['damas']->id, 'since' => '2026-01-01']);
});

test('un miembro de dos comités lo ven los dos, y nadie más', function () {
    $this->actingAs($this->e['liderSonido'])->get('/members')->assertShows('Hermana de Dos Comités');
    $this->actingAs($this->e['presidentaDamas'])->get('/members')->assertShows('Hermana de Dos Comités');
    $this->actingAs($this->e['liderCoro'])->get('/members')->assertDoesntShow('Hermana de Dos Comités');
});

test('cada uno ve solo las pertenencias de su ámbito: Damas no se entera de que también está en Sonido', function () {
    $this->actingAs($this->e['presidentaDamas'])->get('/members')
        ->assertInertia(fn (Assert $page) => $page->where('members.0.committees', fn ($comites) => collect($comites)->pluck('name')->all() === ['Comité de Damas Dorcas']))
        ->assertDoesntShow('Sub-comité de Sonido');
});

test('se cuenta una sola vez, aunque esté en dos nodos del mismo ámbito', function () {
    CommitteeMember::create(['site_id' => $this->e['sede']->id, 'member_id' => $this->doble->id, 'committee_id' => $this->e['coro']->id, 'since' => '2026-01-01']);

    $this->actingAs($this->e['presidente'])->get('/members')
        ->assertInertia(fn (Assert $page) => $page->where('totalCount', 1)->has('members.0.committees', 2));
});

test('CL-27: el pastor ve el padrón completo, también a quien no está en ningún comité', function () {
    Member::factory()->forSite($this->e['sede'])->create(['name' => 'Hermano Sin Lista']);

    $this->actingAs($this->e['pastor'])->get('/members')
        ->assertInertia(fn (Assert $page) => $page->where('totalCount', 2))
        ->assertShows('Hermano Sin Lista');
    $this->actingAs($this->e['presidente'])->get('/members')->assertDoesntShow('Hermano Sin Lista');
});

test('una pertenencia cerrada ya no da acceso: el comité deja de ver a la persona', function () {
    CommitteeMember::where('member_id', $this->doble->id)->where('committee_id', $this->e['damas']->id)->update(['ended_at' => '2026-06-01']);

    $this->actingAs($this->e['presidentaDamas'])->get('/members')->assertDoesntShow('Hermana de Dos Comités');
    $this->actingAs($this->e['liderSonido'])->get('/members')->assertShows('Hermana de Dos Comités');
});

test('una pertenencia no se borra: se cierra', function () {
    expect(fn () => CommitteeMember::first()->delete())->toThrow(RuntimeException::class);
});
