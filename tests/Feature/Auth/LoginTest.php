<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    RateLimiter::clear('rigoberto|127.0.0.1');
});

it('se entra con el usuario asignado, no con correo', function () {
    $hermano = User::factory()->create([
        'username' => 'rigoberto',
        'password' => Hash::make('clave-temporal'),
        'must_change_password' => false,
    ]);

    $this->post('/login', ['username' => 'rigoberto', 'password' => 'clave-temporal'])
        ->assertRedirect();

    $this->assertAuthenticatedAs($hermano);
});

it('el usuario o la clave equivocados lo dicen en una frase clara', function () {
    User::factory()->create(['username' => 'rigoberto', 'password' => Hash::make('clave-temporal')]);

    $this->post('/login', ['username' => 'rigoberto', 'password' => 'otra'])
        ->assertSessionHasErrors(['username' => 'El usuario o la clave no coinciden. Revise e intente de nuevo.']);

    $this->assertGuest();
});

it('quien quedó sin acceso no entra, aunque la clave sea correcta', function () {
    User::factory()->create([
        'username' => 'retirado',
        'password' => Hash::make('clave-temporal'),
        'active' => false,
    ]);

    $this->post('/login', ['username' => 'retirado', 'password' => 'clave-temporal']);

    $this->assertGuest();
});

it('al sexto intento bloquea quince minutos y nombra al pastor', function () {
    User::factory()->create(['username' => 'rigoberto', 'password' => Hash::make('clave-temporal')]);

    foreach (range(1, 5) as $intento) {
        $this->post('/login', ['username' => 'rigoberto', 'password' => 'mala']);
    }

    $respuesta = $this->post('/login', ['username' => 'rigoberto', 'password' => 'clave-temporal']);

    $errores = session('errors')->get('username');

    expect($errores[0])->toContain('bloqueado')
        ->and($errores[0])->toContain('pastor');

    $this->assertGuest();
});

it('ya no existe el registro público ni la recuperación por correo', function () {
    $this->get('/register')->assertNotFound();
    $this->get('/forgot-password')->assertNotFound();
});

it('deja anotada la fecha del último ingreso', function () {
    $hermano = User::factory()->create([
        'username' => 'rigoberto',
        'password' => Hash::make('clave-temporal'),
        'must_change_password' => false,
        'last_login_at' => null,
    ]);

    $this->post('/login', ['username' => 'rigoberto', 'password' => 'clave-temporal']);

    expect($hermano->fresh()->last_login_at)->not->toBeNull();
});
