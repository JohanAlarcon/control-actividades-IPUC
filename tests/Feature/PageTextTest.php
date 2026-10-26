<?php

use PHPUnit\Framework\AssertionFailedError;

/**
 * Por que las pruebas de privacidad usan assertDoesntShow y no assertDontSee: con
 * tildes, assertDontSee no puede fallar nunca, y una prueba que no puede fallar no
 * protege nada.
 */
beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->respuesta = $this->actingAs($this->e['pastor'])->get('/committees');
});

it('assertDontSee no ve un nombre con tilde aunque esté en la pantalla', function () {
    // «Comité de Damas Dorcas» SI esta en la pantalla del pastor, y aun asi esto pasa.
    $this->respuesta->assertDontSee('Comité de Damas Dorcas');
});

it('assertShows sí lo encuentra', function () {
    $this->respuesta->assertShows('Comité de Damas Dorcas');
});

it('assertDoesntShow falla cuando el nombre está, que es justo lo que tiene que hacer', function () {
    expect(fn () => $this->respuesta->assertDoesntShow('Comité de Damas Dorcas'))
        ->toThrow(AssertionFailedError::class);
});
