<?php

use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\Site;
use App\Models\User;

beforeEach(function () {
    $this->site = Site::factory()->create();
    $this->pastor = User::factory()->create(['site_id' => $this->site->id, 'is_pastor' => true]);

    $comite = Committee::factory()->for($this->site)->create();
    $this->presidente = User::factory()->create(['site_id' => $this->site->id]);
    CommitteeUser::factory()->forCommittee($comite)->create(['user_id' => $this->presidente->id]);

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

it('un presidente no asigna ni retira cargos', function () {
    $comite = Committee::factory()->for($this->site)->create();

    $this->actingAs($this->presidente)
        ->post("/committees/{$comite->id}/roles", ['user_id' => $this->cuenta->id, 'role' => CommitteeUser::PRESIDENT])
        ->assertForbidden();

    expect(CommitteeUser::where('committee_id', $comite->id)->exists())->toBeFalse();
});

it('el pastor sí ve la lista de cuentas', function () {
    $this->actingAs($this->pastor)->get('/users')->assertOk();
});

it('el pastor no puede retirarse el acceso a sí mismo', function () {
    $this->actingAs($this->pastor)->post("/users/{$this->pastor->id}/revoke")->assertForbidden();

    expect($this->pastor->fresh()->active)->toBeTrue();
});

it('el pastor de otra sede no toca las cuentas de esta', function () {
    $otraSede = Site::factory()->create();
    $otroPastor = User::factory()->create(['site_id' => $otraSede->id, 'is_pastor' => true]);

    $this->actingAs($otroPastor)->post("/users/{$this->cuenta->id}/revoke")->assertForbidden();
    $this->actingAs($otroPastor)->post("/users/{$this->cuenta->id}/reset-password")->assertForbidden();

    expect($this->cuenta->fresh()->active)->toBeTrue();
});
