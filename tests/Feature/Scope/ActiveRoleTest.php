<?php

use App\Models\Activity;
use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\Site;
use App\Models\User;

beforeEach(function () {
    $this->site = Site::factory()->create();
    $this->alabanza = Committee::factory()->for($this->site)->create();
    $this->damas = Committee::factory()->for($this->site)->create();

    Activity::factory()->forCommittee($this->alabanza)->create(['title' => 'Ensayo del coro']);
    Activity::factory()->forCommittee($this->damas)->create(['title' => 'Reunión de Damas Dorcas']);

    $this->hermano = User::factory()->create([
        'site_id' => $this->site->id,
        'must_change_password' => false,
    ]);

    $this->enAlabanza = CommitteeUser::factory()->forCommittee($this->alabanza)
        ->create(['user_id' => $this->hermano->id]);
    $this->enDamas = CommitteeUser::factory()->forCommittee($this->damas)
        ->create(['user_id' => $this->hermano->id]);
});

it('con dos cargos y ninguno escogido, el sistema pide escoger', function () {
    $this->actingAs($this->hermano)->get('/dashboard')
        ->assertInertia(fn ($page) => $page
            ->where('scope.mustChoose', true)
            ->where('scope.canSwitch', true)
            ->where('scope.activeRole', null)
        );
});

it('al escoger un cargo, solo ve ese ámbito', function () {
    $this->actingAs($this->hermano)->post('/role/switch', ['roleId' => $this->enAlabanza->id]);

    $this->actingAs($this->hermano)->get('/dashboard')
        ->assertInertia(fn ($page) => $page
            ->where('scope.mustChoose', false)
            ->where('scope.activeRole.committeeId', $this->alabanza->id)
            ->where('scope.label', $this->alabanza->name)
        );
});

it('al cambiar de cargo, cambia el ámbito completo y nunca ve los dos juntos', function () {
    $this->actingAs($this->hermano)->post('/role/switch', ['roleId' => $this->enAlabanza->id]);
    expect(app(App\Support\Scope\CurrentScope::class))->toBeInstanceOf(App\Support\Scope\CurrentScope::class);

    $this->actingAs($this->hermano)->post('/role/switch', ['roleId' => $this->enDamas->id]);

    $this->actingAs($this->hermano)->get('/dashboard')
        ->assertInertia(fn ($page) => $page->where('scope.label', $this->damas->name));
});

it('un cargo de otra persona no cambia nada y responde que no está disponible', function () {
    $otro = User::factory()->create(['site_id' => $this->site->id]);
    $otroComite = Committee::factory()->for($this->site)->create();
    $cargoAjeno = CommitteeUser::factory()->forCommittee($otroComite)->create(['user_id' => $otro->id]);

    $this->actingAs($this->hermano)
        ->post('/role/switch', ['roleId' => $cargoAjeno->id])
        ->assertSessionHas('flash.type', 'error');

    $this->actingAs($this->hermano)->get('/dashboard')
        ->assertInertia(fn ($page) => $page->where('scope.mustChoose', true));
});

it('un cargo ya cerrado no se puede escoger', function () {
    $this->enAlabanza->update(['ended_at' => now()]);

    $this->actingAs($this->hermano)
        ->post('/role/switch', ['roleId' => $this->enAlabanza->id])
        ->assertSessionHas('flash.type', 'error');
});

it('los conteos de lo oculto llevan números, nunca nombres', function () {
    $this->actingAs($this->hermano)->post('/role/switch', ['roleId' => $this->enAlabanza->id]);

    $respuesta = $this->actingAs($this->hermano)
        ->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Partial-Component' => 'dashboard', 'X-Inertia-Partial-Data' => 'scope.hidden'])
        ->get('/dashboard');

    $contenido = $respuesta->getContent();

    expect($contenido)->not->toContain($this->damas->name);
});
