<?php

use Inertia\Testing\AssertableInertia as Assert;

/*
 * La policy mas estrecha del proyecto (T112, T176; RF-040, R-06; HU-13 escenario 2): la ficha de
 * seguimiento la abren solo el pastor y quien preside Evangelismo. Ni quien registro a la persona,
 * ni el lider de un sub-comite de Evangelismo, ni su secretario: tampoco por su numero.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->ev = evangelismoDePrueba($this->e);
    $this->persona = simpatizanteDePrueba($this->ev, ['name' => 'Andrea Milena Castro', 'invited_by_user_id' => $this->e['liderSonido']->id]);
});

test('2. quien la registró no ve su ficha, y la sección le explica por qué sin que parezca un error', function () {
    $this->actingAs($this->e['liderSonido'])->get('/sympathizers')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Sympathizers/Index')
            ->where('canFollow', false)
            ->where('followers', 'Comité de Evangelismo')
            ->missing('list'))
        ->assertDoesntShow('Andrea Milena Castro');

    $this->actingAs($this->e['liderSonido'])->get("/sympathizers/{$this->persona->id}")->assertNotFound();
});

test('ni el líder de un sub-comité de Evangelismo ni su secretario la abren por su número', function (string $quien, int $estado) {
    $this->actingAs($this->ev[$quien])->get("/sympathizers/{$this->persona->id}")
        ->assertStatus($estado)
        ->assertInertia(fn (Assert $page) => $page->component('Errors/OutOfScope'));
})->with([
    'líder de Visitación' => ['liderVisitacion', 404],
    'secretario de Evangelismo' => ['secretarioEvangelismo', 403],
]);

test('nadie más anota etapas ni visitas', function () {
    $this->actingAs($this->ev['secretarioEvangelismo'])->post("/sympathizers/{$this->persona->id}/stages", ['stage' => 2, 'reached_at' => now()->toDateString()])->assertForbidden();
    $this->actingAs($this->ev['liderVisitacion'])->post("/sympathizers/{$this->persona->id}/visits", ['visited_at' => now()->toDateString(), 'type' => 'visit'])->assertNotFound();

    expect($this->persona->stages()->count())->toBe(1)->and($this->persona->visits()->count())->toBe(0);
});

test('quien preside Evangelismo y el pastor sí la acompañan', function (string $quien) {
    $actor = $quien === 'pastor' ? $this->e['pastor'] : $this->ev['presidenteEvangelismo'];

    $this->actingAs($actor)->get("/sympathizers/{$this->persona->id}")->assertOk();
    $this->actingAs($actor)->get('/sympathizers')->assertInertia(fn (Assert $page) => $page->where('canFollow', true)->has('list', 1));
})->with(['presidente de Evangelismo', 'pastor']);
