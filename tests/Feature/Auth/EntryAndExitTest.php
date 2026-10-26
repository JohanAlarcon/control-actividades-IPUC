<?php

use App\Models\User;

it('la dirección raíz lleva a la pantalla de entrada a quien no ha entrado', function () {
    $this->get('/')->assertRedirect('/login');
});

it('la dirección raíz lleva al inicio a quien ya entró', function () {
    $this->actingAs(User::factory()->create())->get('/')->assertRedirect('/dashboard');
});

it('al salir, el sistema vuelve a la pantalla de entrada y no a una página en inglés', function () {
    $this->actingAs(User::factory()->create())->post('/logout')->assertRedirect('/login');

    $this->assertGuest();
});

it('cambiar la clave por gusto avisa por el mismo canal que leen las pantallas', function () {
    $this->actingAs(User::factory()->create())
        ->post('/password/change', ['password' => 'clave-nueva-123', 'password_confirmation' => 'clave-nueva-123'])
        ->assertRedirect('/dashboard')
        ->assertSessionHas('notice', 'Su clave quedó cambiada. Use la nueva la próxima vez que entre.');
});
