<?php

use App\Models\User;

/**
 * El aviso de version nueva (T047) compara dos huellas: la que trae cada pantalla y la que
 * publica /version. Si el servidor diera otra cosa, el aviso saldria siempre o nunca.
 */
it('la versión que publica el servidor es la misma con la que se sirvió la pantalla', function () {
    $pagina = $this->actingAs(User::factory()->create())->get('/dashboard')->viewData('page');

    $this->getJson('/version')
        ->assertOk()
        ->assertExactJson(['version' => $pagina['version']]);
});

it('la versión se puede consultar sin haber entrado, y no dice nada más que eso', function () {
    $this->getJson('/version')
        ->assertOk()
        ->assertJsonStructure(['version'])
        ->assertJsonCount(1);
});
