<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\Site;
use App\Models\User;

/**
 * La cara visible de RF-008: lo que no se puede ver responde con una pagina en
 * espanol que explica la situacion y no trae un solo dato de lo que se pidio.
 */
beforeEach(function () {
    $this->site = Site::factory()->create();
    $damas = Committee::factory()->for($this->site)->create();

    $this->presidenta = User::factory()->create(['site_id' => $this->site->id]);
    CommitteeUser::factory()->forCommittee($damas)->create(['user_id' => $this->presidenta->id]);

    $this->ajeno = User::factory()->create([
        'site_id' => $this->site->id,
        'name' => 'Hermano Ajeno Ejemplo',
        'username' => 'ajeno.ejemplo',
    ]);
});

it('lo que un cargo no puede ver muestra la página en español, no un error técnico', function () {
    $this->actingAs($this->presidenta)->get('/users')
        ->assertForbidden()
        ->assertInertia(fn ($page) => $page->component('Errors/OutOfScope'))
        ->assertDontSee('This action is unauthorized');
});

it('la respuesta no trae ningún dato de lo que se intentó abrir', function () {
    $this->actingAs($this->presidenta)->get("/users/{$this->ajeno->id}/edit")
        ->assertForbidden()
        ->assertInertia(fn ($page) => $page->component('Errors/OutOfScope'))
        ->assertDontSee('Hermano Ajeno Ejemplo')
        ->assertDontSee('ajeno.ejemplo');
});

it('una cuenta de otra sede responde exactamente igual que una que no existe', function () {
    $pastor = User::factory()->create(['site_id' => $this->site->id, 'is_pastor' => true]);
    $deOtraSede = User::factory()->create(['site_id' => Site::factory()->create()->id, 'name' => 'Hermano De Otra Sede']);

    $ajena = $this->actingAs($pastor)->get("/users/{$deOtraSede->id}/edit");
    $inexistente = $this->actingAs($pastor)->get('/users/999999/edit');

    // La direccion pedida es lo unico que cambia entre las dos respuestas.
    $pagina = fn ($respuesta) => collect($respuesta->viewData('page'))->except('url')->all();

    $ajena->assertNotFound()->assertDontSee('Hermano De Otra Sede');
    expect($inexistente->status())->toBe(404)
        ->and($pagina($ajena))->toBe($pagina($inexistente));
});

it('una dirección que no existe también muestra la página en español', function () {
    $this->actingAs($this->presidenta)->get('/esto-no-existe')
        ->assertNotFound()
        ->assertInertia(fn ($page) => $page->component('Errors/OutOfScope'));
});

it('también responde bien cuando la aplicación navega sin recargar la página', function () {
    $version = (string) app(HandleInertiaRequests::class)->version(request());

    $this->actingAs($this->presidenta)
        ->get('/users', ['X-Inertia' => 'true', 'X-Inertia-Version' => $version])
        ->assertForbidden()
        ->assertJson(['component' => 'Errors/OutOfScope']);
});
