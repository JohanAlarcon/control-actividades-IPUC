<?php

use App\Models\Committee;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Prueba de humo del hito 1 (T050): el recorrido completo de una persona nueva, con cada uno
 * de los tres cargos. Entra con la clave temporal que le dio el pastor, escoge la suya, ve su
 * parte de la congregacion, y si intenta abrir lo ajeno recibe el mensaje de alcance.
 */
beforeEach(function () {
    $this->e = estructuraDePrueba();
});

/** Entrar por primera vez: la clave temporal lleva a escoger una propia, y despues al inicio. */
function primerIngreso($prueba, User $persona): void
{
    $persona->update(['username' => 'humo.'.$persona->id, 'password' => Hash::make('temporal1234'), 'must_change_password' => true]);

    $prueba->post('/login', ['username' => 'humo.'.$persona->id, 'password' => 'temporal1234']);
    $prueba->assertAuthenticatedAs($persona);
    $prueba->get('/dashboard')->assertRedirect('/password/change');

    $prueba->post('/password/change', ['password' => 'mi-clave-propia', 'password_confirmation' => 'mi-clave-propia'])
        ->assertRedirect('/dashboard');
    $prueba->get('/dashboard')->assertOk()->assertInertia(fn ($pagina) => $pagina->component('dashboard'));
}

it('el pastor entra, escoge su clave, ve toda la congregación, y otra sede le responde el mensaje', function () {
    primerIngreso($this, $this->e['pastor']);

    $this->get('/committees')->assertOk()
        ->assertShows('Comité de Alabanza')
        ->assertShows('Comité de Damas Dorcas');

    $deOtraSede = Committee::factory()->for(Site::factory()->create())->create(['name' => 'Comité de Otra Sede']);

    $this->get("/committees/{$deOtraSede->id}")
        ->assertNotFound()
        ->assertInertia(fn ($pagina) => $pagina->component('Errors/OutOfScope'))
        ->assertDoesntShow('Comité de Otra Sede');
});

it('un presidente entra, escoge su clave, ve su comité, y uno ajeno le responde el mensaje', function () {
    primerIngreso($this, $this->e['presidente']);

    $this->get('/committees')->assertOk()
        ->assertShows('Comité de Alabanza')
        ->assertShows('Sub-comité de Sonido')
        ->assertDoesntShow('Comité de Damas Dorcas');

    $this->get("/committees/{$this->e['damas']->id}")
        ->assertNotFound()
        ->assertInertia(fn ($pagina) => $pagina->component('Errors/OutOfScope'))
        ->assertDoesntShow('Comité de Damas Dorcas');
});

it('un líder entra, escoge su clave, ve su sub-comité, y el comité de arriba le responde el mensaje', function () {
    primerIngreso($this, $this->e['liderSonido']);

    $this->get('/committees')->assertOk()
        ->assertShows('Sub-comité de Sonido')
        ->assertShows('Hermano Rigoberto Páez')
        ->assertDoesntShow('Comité de Alabanza')
        ->assertDoesntShow('Sub-comité de Coro');

    $this->get("/committees/{$this->e['alabanza']->id}")
        ->assertNotFound()
        ->assertInertia(fn ($pagina) => $pagina->component('Errors/OutOfScope'))
        ->assertDoesntShow('Comité de Alabanza');
});
