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

/** Lo que manda el navegador al pedir una propiedad diferida, version incluida: sin ella Inertia responde 409. */
function pedidoParcial(string $propiedad): array
{
    return [
        'X-Inertia' => 'true',
        'X-Inertia-Version' => (string) app(App\Http\Middleware\HandleInertiaRequests::class)->version(request()),
        'X-Inertia-Partial-Component' => 'dashboard',
        'X-Inertia-Partial-Data' => $propiedad,
    ];
}

it('los conteos de lo oculto llevan números, nunca nombres', function () {
    $this->actingAs($this->hermano)->post('/role/switch', ['roleId' => $this->enAlabanza->id]);

    $respuesta = $this->actingAs($this->hermano)
        ->withHeaders(pedidoParcial('scopeHidden'))
        ->get('/dashboard')
        ->assertOk();

    $contenido = $respuesta->getContent();

    expect($contenido)->not->toContain($this->damas->name);
});

it('el conteo diferido llega aparte y no borra el cargo activo al mezclarse', function () {
    $this->actingAs($this->hermano)->post('/role/switch', ['roleId' => $this->enAlabanza->id]);

    $props = $this->actingAs($this->hermano)
        ->withHeaders(pedidoParcial('scopeHidden'))
        ->get('/dashboard')
        ->assertOk()
        ->json('props');

    // El navegador mezcla solo el primer nivel: si la respuesta trajera «scope»,
    // reemplazaria el que ya tiene y se perderian el cargo activo y la etiqueta.
    expect($props)->toHaveKey('scopeHidden.committees')
        ->and($props)->not->toHaveKey('scope');
});
