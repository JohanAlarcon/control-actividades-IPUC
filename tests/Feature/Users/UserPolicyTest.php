<?php

use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\Site;
use App\Models\User;

beforeEach(function () {
    $this->site = Site::factory()->create();
    $this->pastor = User::factory()->create(['site_id' => $this->site->id, 'is_pastor' => true]);

    $this->comite = Committee::factory()->for($this->site)->create();
    $this->presidente = User::factory()->create(['site_id' => $this->site->id]);
    $this->cargo = CommitteeUser::factory()->forCommittee($this->comite)->create(['user_id' => $this->presidente->id]);

    $this->cuenta = User::factory()->create(['site_id' => $this->site->id]);
});

it('un presidente no ve la lista de cuentas', function () {
    $this->actingAs($this->presidente)->get('/users')->assertForbidden();
});

it('un presidente no crea cuentas', function () {
    $this->actingAs($this->presidente)->get('/users/create')->assertForbidden();

    $this->actingAs($this->presidente)
        ->post('/users', ['name' => 'Hermano Nuevo', 'username' => 'hermano.nuevo'])
        ->assertForbidden();

    expect(User::where('username', 'hermano.nuevo')->exists())->toBeFalse();
});

it('un presidente no modifica cuentas', function () {
    $this->actingAs($this->presidente)
        ->patch("/users/{$this->cuenta->id}", ['name' => 'Otro Nombre', 'username' => 'otro.nombre'])
        ->assertForbidden();

    expect($this->cuenta->fresh()->name)->not->toBe('Otro Nombre');
});

it('un presidente no restablece claves ni retira cuentas', function () {
    $this->actingAs($this->presidente)->post("/users/{$this->cuenta->id}/reset-password")->assertForbidden();
    $this->actingAs($this->presidente)->post("/users/{$this->cuenta->id}/revoke")->assertForbidden();

    expect($this->cuenta->fresh()->active)->toBeTrue();
});

it('un presidente no asigna cargos, ni en su comité ni en uno ajeno', function () {
    $ajeno = Committee::factory()->for($this->site)->create();
    $datos = ['user_id' => $this->cuenta->id, 'role' => CommitteeUser::SECRETARY];

    // En su comite lo frena el permiso; un comite ajeno, para el, no existe.
    $this->actingAs($this->presidente)->post("/committees/{$this->comite->id}/roles", $datos)->assertForbidden();
    $this->actingAs($this->presidente)->post("/committees/{$ajeno->id}/roles", $datos)->assertNotFound();

    expect($this->cuenta->currentRoles()->exists())->toBeFalse();
});

it('un presidente no retira cargos, ni siquiera el suyo', function () {
    $this->actingAs($this->presidente)->delete("/roles/{$this->cargo->id}")->assertForbidden();

    expect($this->cargo->fresh()->ended_at)->toBeNull();
});

it('el pastor sí ve la lista de cuentas', function () {
    $this->actingAs($this->pastor)->get('/users')->assertOk();
});

it('el pastor no puede retirarse el acceso a sí mismo', function () {
    $this->actingAs($this->pastor)->post("/users/{$this->pastor->id}/revoke")->assertForbidden();

    expect($this->pastor->fresh()->active)->toBeTrue();
});

it('el pastor de otra sede no toca las cuentas de esta: para él no existen', function () {
    $otraSede = Site::factory()->create();
    $otroPastor = User::factory()->create(['site_id' => $otraSede->id, 'is_pastor' => true]);

    $this->actingAs($otroPastor)->post("/users/{$this->cuenta->id}/revoke")->assertNotFound();
    $this->actingAs($otroPastor)->post("/users/{$this->cuenta->id}/reset-password")->assertNotFound();

    expect($this->cuenta->fresh()->active)->toBeTrue();
});
