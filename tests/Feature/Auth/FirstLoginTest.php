<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('con la clave temporal sin cambiar, ninguna otra pantalla responde', function () {
    $hermano = User::factory()->create(['must_change_password' => true]);

    $this->actingAs($hermano)->get('/dashboard')->assertRedirect('/password/change');
    $this->actingAs($hermano)->get('/')->assertRedirect('/password/change');
});

it('la pantalla de cambio sí responde, y avisa que es obligatorio', function () {
    $hermano = User::factory()->create(['must_change_password' => true]);

    $this->actingAs($hermano)->get('/password/change')
        ->assertInertia(fn ($page) => $page->component('auth/ChangePassword')->where('forced', true));
});

it('al cambiar la clave, deja de exigirlo y lleva al inicio', function () {
    $hermano = User::factory()->create(['must_change_password' => true]);

    $this->actingAs($hermano)
        ->post('/password/change', ['password' => 'clave-nueva-123', 'password_confirmation' => 'clave-nueva-123'])
        ->assertRedirect('/dashboard');

    expect($hermano->fresh()->must_change_password)->toBeFalse()
        ->and(Hash::check('clave-nueva-123', $hermano->fresh()->password))->toBeTrue();
});

it('si las dos claves no coinciden, lo explica sin tecnicismos', function () {
    $hermano = User::factory()->create(['must_change_password' => true]);

    $this->actingAs($hermano)
        ->post('/password/change', ['password' => 'clave-nueva-123', 'password_confirmation' => 'otra-cosa'])
        ->assertSessionHasErrors(['password' => 'Las dos claves no coinciden. Escríbalas de nuevo.']);

    expect($hermano->fresh()->must_change_password)->toBeTrue();
});

it('quien ya cambió su clave entra directo a su pantalla de inicio', function () {
    $hermano = User::factory()->create(['must_change_password' => false]);

    $this->actingAs($hermano)->get('/dashboard')->assertOk();
});

it('tras un restablecimiento del pastor, vuelve a exigir el cambio', function () {
    $hermano = User::factory()->create(['must_change_password' => false]);

    $hermano->forceFill([
        'password' => Hash::make('temporal-nueva'),
        'must_change_password' => true,
    ])->save();

    $this->actingAs($hermano)->get('/dashboard')->assertRedirect('/password/change');
});
