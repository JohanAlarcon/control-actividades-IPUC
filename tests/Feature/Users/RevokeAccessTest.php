<?php

use App\Models\Approval;
use App\Models\Committee;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->site = Site::factory()->create();
    $this->pastor = User::factory()->create(['site_id' => $this->site->id, 'is_pastor' => true]);
    $this->lider = User::factory()->create([
        'site_id' => $this->site->id,
        'username' => 'lider.sonido',
        'password' => Hash::make('clave1234'),
    ]);
});

it('la persona retirada pierde la sesión en la petición siguiente', function () {
    $this->post('/login', ['username' => 'lider.sonido', 'password' => 'clave1234']);
    $this->assertAuthenticatedAs($this->lider);

    // El pastor la retira desde su propio celular: lo que cambia es la base.
    User::whereKey($this->lider->id)->update(['active' => false]);
    app('auth')->forgetGuards();

    $this->get('/dashboard')->assertRedirect('/login');
    $this->assertGuest();
});

it('la persona retirada no puede volver a entrar', function () {
    $this->actingAs($this->pastor)->post("/users/{$this->lider->id}/revoke");
    app('auth')->forgetGuards();
    $this->post('/logout');

    $this->post('/login', ['username' => 'lider.sonido', 'password' => 'clave1234']);

    $this->assertGuest();
});

it('la cuenta no se borra y su nombre sigue en lo que pidió', function () {
    $comite = Committee::factory()->for($this->site)->create();
    $solicitud = Approval::factory()->forCommittee($comite)->create(['requested_by_user_id' => $this->lider->id]);

    $this->actingAs($this->pastor)->post("/users/{$this->lider->id}/revoke")
        ->assertRedirect('/users')
        ->assertSessionHas('notice');

    expect(User::find($this->lider->id))->not->toBeNull()
        ->and(User::find($this->lider->id)->active)->toBeFalse()
        ->and($solicitud->fresh()->requested_by_user_id)->toBe($this->lider->id);
});

it('al habilitarla otra vez, recibe una clave temporal nueva', function () {
    $this->lider->update(['active' => false]);

    $this->actingAs($this->pastor)->post("/users/{$this->lider->id}/restore")
        ->assertSessionHas('temporaryPassword');

    $lider = $this->lider->fresh();
    expect($lider->active)->toBeTrue()
        ->and($lider->must_change_password)->toBeTrue()
        ->and(Hash::check('clave1234', $lider->password))->toBeFalse();
});
