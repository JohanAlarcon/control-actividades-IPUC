<?php

use Inertia\Testing\AssertableInertia as Assert;

/*
 * Quién ve y acompaña a un simpatizante (T112, T176, T216; RF-040, D-34; HU-13 escenario 1): el
 * comité al que está asignado —cualquiera de sus cargos—, BIS y el pastor. Otro comité no lo ve, ni
 * siquiera por su número, ni le anota etapas ni visitas.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->ev = evangelismoDePrueba($this->e);
    $this->bis = bisDePrueba($this->e);
    $this->persona = simpatizanteDePrueba($this->ev, ['name' => 'Andrea Milena Castro', 'invited_by_user_id' => $this->e['liderSonido']->id]);
});

test('otro comité no la ve, ni siquiera quien la invitó: la lista se le muestra sin ella', function () {
    $this->actingAs($this->e['liderSonido'])->get('/sympathizers')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Sympathizers/Index')->has('list', 0))
        ->assertDoesntShow('Andrea Milena Castro');

    $this->actingAs($this->e['liderSonido'])->get("/sympathizers/{$this->persona->id}")->assertNotFound();
});

test('nadie de otro comité le anota etapas ni visitas', function () {
    $this->actingAs($this->e['presidentaDamas'])->post("/sympathizers/{$this->persona->id}/stages", ['stage' => 2, 'reached_at' => now()->toDateString()])->assertNotFound();
    $this->actingAs($this->e['liderSonido'])->post("/sympathizers/{$this->persona->id}/visits", ['visited_at' => now()->toDateString(), 'type' => 'visit'])->assertNotFound();

    expect($this->persona->stages()->count())->toBe(1)->and($this->persona->visits()->count())->toBe(0);
});

test('el comité asignado la acompaña con cualquiera de sus cargos, y también su sub-comité si se la asignan a él', function (string $quien) {
    $this->actingAs($this->ev[$quien])->get("/sympathizers/{$this->persona->id}")->assertOk();
    $this->actingAs($this->ev[$quien])->get('/sympathizers')->assertInertia(fn (Assert $page) => $page->has('list', 1)->where('list.0.committee', 'Comité de Evangelismo'));
})->with(['presidenteEvangelismo', 'secretarioEvangelismo']);

test('el líder de un sub-comité no ve lo asignado al comité de arriba', function () {
    $this->actingAs($this->ev['liderVisitacion'])->get("/sympathizers/{$this->persona->id}")->assertNotFound();
});

test('BIS y el pastor la ven, y de toda la sede por comité', function (string $quien) {
    $actor = $quien === 'pastor' ? $this->e['pastor'] : $this->bis['secretarioBis'];

    $this->actingAs($actor)->get("/sympathizers/{$this->persona->id}")->assertOk();
    $this->actingAs($actor)->get('/sympathizers')
        ->assertInertia(fn (Assert $page) => $page->has('list', 1)->where('byCommittee.0.committee', 'Comité de Evangelismo')->where('byCommittee.0.total', 1));
})->with(['secretario de BIS', 'pastor']);
