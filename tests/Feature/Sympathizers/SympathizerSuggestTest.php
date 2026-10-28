<?php

/*
 * La lista que se completa sola (T217; RF-096, HU-13 escenario 2, CL-09): mientras se escribe el
 * nombre, salen los ya registrados del ámbito —a BIS, todos; a un comité, los suyos—. De afuera, el
 * aviso de duplicado solo dice sí o no.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->ev = evangelismoDePrueba($this->e);
    $this->bis = bisDePrueba($this->e);
    simpatizanteDePrueba($this->ev, ['name' => 'Andrea Milena Castro', 'phone' => '320 555 3400']);
});

test('un comité nunca recibe en las sugerencias a un simpatizante de otro', function () {
    $this->actingAs($this->e['liderSonido'])->getJson('/sympathizers/suggest?q=andrea')->assertExactJson(['results' => []]);
});

test('el comité asignado y BIS sí la reciben, con su teléfono para reconocerla', function (string $quien) {
    $actor = $quien === 'BIS' ? $this->bis['secretarioBis'] : $this->ev['presidenteEvangelismo'];

    $resultados = $this->actingAs($actor)->getJson('/sympathizers/suggest?q=mil')->assertOk()->json('results');

    expect($resultados)->toHaveCount(1)
        ->and(array_keys($resultados[0]))->toBe(['id', 'name', 'phone'])
        ->and($resultados[0]['phone'])->toBe('320 555 3400');
})->with(['BIS', 'Evangelismo']);

test('busca por el comienzo de cada palabra, y pide al menos dos letras', function () {
    $this->actingAs($this->bis['secretarioBis'])->getJson('/sympathizers/suggest?q=CAS')->assertJsonCount(1, 'results');
    $this->actingAs($this->bis['secretarioBis'])->getJson('/sympathizers/suggest?q=ena')->assertExactJson(['results' => []]);
    $this->actingAs($this->bis['secretarioBis'])->getJson('/sympathizers/suggest?q=a')->assertExactJson(['results' => []]);
});

test('el aviso de duplicado de afuera no trae nombre de comité ni datos de la otra ficha', function () {
    $this->actingAs($this->e['liderSonido'])->post('/sympathizers', [
        'name' => 'Andrea Milena Castro',
        'age_mode' => 'age',
        'age_reported' => 28,
        'gender' => 'f',
        'phone' => '3205553400',
        'arrival_source' => 'invitation',
        'first_contact_at' => now()->toDateString(),
    ])->assertSessionHasErrors('duplicate');

    expect(session('errors')->first('duplicate'))->not->toContain('Evangelismo')->not->toContain('Picaleña');
});
