<?php

use App\Models\Activity;
use App\Models\Approval;
use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\Site;
use App\Models\User;
use App\Support\Scope\CurrentScope;

/**
 * Principio I, NO NEGOCIABLE. Ninguna de estas pruebas escribe "where committee_id":
 * si el filtro no viniera puesto, todas fallarian.
 */
beforeEach(function () {
    $this->site = Site::factory()->create();

    $this->alabanza = Committee::factory()->for($this->site)->create();
    $this->sonido = Committee::factory()->childOf($this->alabanza)->create();
    $this->montaje = Committee::factory()->childOf($this->sonido)->create();
    $this->coro = Committee::factory()->childOf($this->alabanza)->create();
    $this->damas = Committee::factory()->for($this->site)->create();

    foreach ([$this->alabanza, $this->sonido, $this->montaje, $this->coro, $this->damas] as $nodo) {
        Activity::factory()->forCommittee($nodo)->create(['title' => 'Actividad de '.$nodo->name]);
        Approval::factory()->forCommittee($nodo)->create();
    }
});

function actuandoComo(User $usuario, ?int $cargoId = null): void
{
    app(CurrentScope::class)->resolve($usuario, $cargoId);
}

function cargoEn(Committee $comite, string $role = CommitteeUser::PRESIDENT): User
{
    $usuario = User::factory()->create(['site_id' => $comite->site_id]);
    CommitteeUser::factory()->forCommittee($comite)->role($role)->create(['user_id' => $usuario->id]);

    return $usuario;
}

it('un presidente solo ve lo de su comité y sus descendientes', function () {
    actuandoComo(cargoEn($this->alabanza));

    expect(Committee::count())->toBe(4)
        ->and(Activity::count())->toBe(4)
        ->and(Approval::count())->toBe(4)
        ->and(Activity::pluck('committee_id')->all())->not->toContain($this->damas->id);
});

it('un líder de sub-comité no ve a su rama hermana', function () {
    actuandoComo(cargoEn($this->sonido, CommitteeUser::LEADER));

    $comites = Committee::pluck('id')->all();

    expect($comites)->toContain($this->sonido->id, $this->montaje->id)
        ->and($comites)->not->toContain($this->coro->id)
        ->and($comites)->not->toContain($this->alabanza->id)
        ->and(Activity::count())->toBe(2);
});

it('un líder de equipo solo ve su equipo', function () {
    actuandoComo(cargoEn($this->montaje, CommitteeUser::LEADER));

    expect(Committee::pluck('id')->all())->toBe([$this->montaje->id])
        ->and(Activity::count())->toBe(1);
});

it('un presidente sí ve a sus nietos', function () {
    actuandoComo(cargoEn($this->alabanza));

    expect(Committee::find($this->montaje->id))->not->toBeNull();
});

it('abrir por identificador directo lo ajeno no devuelve nada', function () {
    $actividadAjena = Activity::where('committee_id', $this->damas->id)->first();

    actuandoComo(cargoEn($this->alabanza));

    expect(Committee::find($this->damas->id))->toBeNull()
        ->and(Activity::find($actividadAjena->id))->toBeNull();
});

it('el pastor sí ve toda la congregación', function () {
    $pastor = User::factory()->create(['site_id' => $this->site->id, 'is_pastor' => true]);

    actuandoComo($pastor);

    expect(Committee::count())->toBe(5)
        ->and(Activity::count())->toBe(5);
});

it('una persona con dos cargos ve un ámbito a la vez, nunca la unión', function () {
    $hermano = User::factory()->create(['site_id' => $this->site->id]);
    $enAlabanza = CommitteeUser::factory()->forCommittee($this->alabanza)->create(['user_id' => $hermano->id]);
    $enDamas = CommitteeUser::factory()->forCommittee($this->damas)->create(['user_id' => $hermano->id]);

    actuandoComo($hermano, $enAlabanza->id);
    expect(Committee::count())->toBe(4)
        ->and(Committee::find($this->damas->id))->toBeNull();

    actuandoComo($hermano, $enDamas->id);
    expect(Committee::count())->toBe(1)
        ->and(Committee::find($this->alabanza->id))->toBeNull();
});

it('la sede de otra congregación nunca se ve', function () {
    $otraSede = Site::factory()->create();
    $comiteAjeno = Committee::factory()->for($otraSede)->create();

    $pastor = User::factory()->create(['site_id' => $this->site->id, 'is_pastor' => true]);
    actuandoComo($pastor);

    expect(Committee::find($comiteAjeno->id))->toBeNull();
});
