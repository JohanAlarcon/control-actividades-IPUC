<?php

use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->site = Site::factory()->create();
    $this->pastor = User::factory()->create(['site_id' => $this->site->id, 'is_pastor' => true]);
    $this->hermana = User::factory()->create([
        'site_id' => $this->site->id,
        'username' => 'secretaria.damas',
        'password' => Hash::make('clave-olvidada'),
    ]);
});

it('el pastor restablece la clave y la vieja deja de servir', function () {
    $this->actingAs($this->pastor)->post("/users/{$this->hermana->id}/reset-password")
        ->assertRedirect("/users/{$this->hermana->id}/edit")
        ->assertSessionHas('temporaryPassword');

    $hermana = $this->hermana->fresh();
    expect($hermana->must_change_password)->toBeTrue()
        ->and(Hash::check('clave-olvidada', $hermana->password))->toBeFalse();
});

it('con la clave temporal entra y el sistema la lleva a escoger una suya', function () {
    $this->actingAs($this->pastor)->post("/users/{$this->hermana->id}/reset-password");
    $clave = session('temporaryPassword')['password'];
    app('auth')->forgetGuards();
    $this->post('/logout');

    $this->post('/login', ['username' => 'secretaria.damas', 'password' => $clave]);
    $this->assertAuthenticatedAs($this->hermana->fresh());

    $this->get('/dashboard')->assertRedirect('/password/change');
});

it('la clave temporal se puede dictar: una palabra y cuatro números', function () {
    $this->actingAs($this->pastor)->post("/users/{$this->hermana->id}/reset-password");

    expect(session('temporaryPassword')['password'])->toMatch('/^[a-z]+\d{4}$/');
});

it('solo el pastor restablece claves', function () {
    $otro = User::factory()->create(['site_id' => $this->site->id]);

    $this->actingAs($otro)->post("/users/{$this->hermana->id}/reset-password")->assertForbidden();

    expect(Hash::check('clave-olvidada', $this->hermana->fresh()->password))->toBeTrue();
});
