<?php

use App\Models\Member;
use App\Models\Site;
use Illuminate\Support\Carbon;

/*
 * El buscador del padrón (T215): la excepción 4 del principio I, constitución 3.0.0. Es una lista
 * blanca: cada resultado trae exactamente el número, el nombre y la edad. Encontrar a alguien no
 * da permiso de abrir su ficha.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-06-15 10:00', 'America/Bogota'));
    $this->ajena = Member::factory()->forCommittee($this->e['damas'])->create([
        'name' => 'Hermana Marleny Ortiz',
        'birth_date' => '1980-06-20',
        'phone' => '310 555 1234',
        'neighborhood' => 'Picaleña',
    ]);
});

test('cada resultado trae exactamente el número, el nombre y la edad', function () {
    $respuesta = $this->actingAs($this->e['liderSonido'])->getJson('/members/picker?q=marleny')->assertOk();

    expect($respuesta->json('results'))->toHaveCount(1)
        ->and(array_keys($respuesta->json('results.0')))->toBe(['id', 'name', 'age'])
        ->and($respuesta->json('results.0'))->toBe(['id' => $this->ajena->id, 'name' => 'Hermana Marleny Ortiz', 'age' => 45])
        ->and($respuesta->getContent())->not->toContain('Picaleña')->not->toContain('555')->not->toContain('Damas');
});

test('encuentra sin importar mayúsculas ni tildes, por el comienzo de cada palabra, y pide al menos dos letras', function () {
    $this->actingAs($this->e['presidente'])->getJson('/members/picker?q=ORTÍZ')->assertJsonCount(1, 'results');
    $this->actingAs($this->e['presidente'])->getJson('/members/picker?q=ma')->assertJsonPath('results.0.name', 'Hermana Marleny Ortiz');
    $this->actingAs($this->e['presidente'])->getJson('/members/picker?q=na')->assertExactJson(['results' => []]);
    $this->actingAs($this->e['presidente'])->getJson('/members/picker?q=m')->assertExactJson(['results' => []]);
});

test('no trae a los retirados ni a los de otra sede', function () {
    $this->ajena->update(['active' => false]);
    Member::factory()->forSite(Site::factory()->create())->create(['name' => 'Hermana Marleny de Otra Sede']);

    $this->actingAs($this->e['presidente'])->getJson('/members/picker?q=marleny')->assertExactJson(['results' => []]);
});

test('encontrarla no da permiso de abrir su ficha: responde no disponible', function () {
    $this->actingAs($this->e['presidente'])->get("/members/{$this->ajena->id}/edit")->assertNotFound();
});
