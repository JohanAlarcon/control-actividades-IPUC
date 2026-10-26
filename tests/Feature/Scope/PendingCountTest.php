<?php

use App\Models\Approval;

/**
 * RF-055 y la regla 5 del contrato de propiedades compartidas: el contador del menu
 * cuenta lo que espera la decision del cargo activo, no todo lo pendiente que ve.
 */
beforeEach(function () {
    $this->e = estructuraDePrueba();

    // Dos esperan al presidente de Alabanza y una ya subio al pastor.
    Approval::factory()->forCommittee($this->e['sonido'])->count(2)->create();
    Approval::factory()->forCommittee($this->e['alabanza'])->create(['level' => Approval::LEVEL_PASTOR]);
    // Una ya resuelta y otra de un comite ajeno: ninguna de las dos le toca a Alabanza.
    Approval::factory()->forCommittee($this->e['coro'])->resolved()->create();
    Approval::factory()->forCommittee($this->e['damas'])->create();
});

function contadorDelMenu($prueba, $persona): int
{
    return $prueba->actingAs($persona)
        ->withHeaders(pedidoParcial('pendingCount'))
        ->get('/dashboard')
        ->assertOk()
        ->json('props.pendingCount');
}

it('un presidente cuenta lo que le toca decidir en su comité', function () {
    expect(contadorDelMenu($this, $this->e['presidente']))->toBe(2);
});

it('el pastor cuenta solo lo que ya subió a su nivel', function () {
    expect(contadorDelMenu($this, $this->e['pastor']))->toBe(1);
});

it('un líder no decide solicitudes: su contador es cero', function () {
    expect(contadorDelMenu($this, $this->e['liderSonido']))->toBe(0);
});

it('una secretaria tampoco decide, aunque vea lo mismo que su presidente', function () {
    expect(contadorDelMenu($this, $this->e['secretaria']))->toBe(0);
});
