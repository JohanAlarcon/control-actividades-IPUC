<?php

use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\Site;
use App\Models\User;
use App\Support\Scope\CurrentScope;

beforeEach(function () {
    $this->scope = app(CurrentScope::class);
    $this->site = Site::factory()->create();

    $this->alabanza = Committee::factory()->for($this->site)->create();
    $this->sonido = Committee::factory()->childOf($this->alabanza)->create();
    $this->coro = Committee::factory()->childOf($this->alabanza)->create();

    $this->damas = Committee::factory()->for($this->site)->create();
});

it('el pastor ve todos los comités de su sede', function () {
    $pastor = User::factory()->create(['site_id' => $this->site->id, 'is_pastor' => true]);

    $this->scope->resolve($pastor);

    expect($this->scope->isPastor())->toBeTrue()
        ->and($this->scope->committeeIds())->toHaveCount(4)
        ->and($this->scope->sees($this->damas->id))->toBeTrue();
});

it('un presidente ve su comité y todos sus sub-comités', function () {
    $presidente = User::factory()->create(['site_id' => $this->site->id]);
    CommitteeUser::factory()->forCommittee($this->alabanza)->create(['user_id' => $presidente->id]);

    $this->scope->resolve($presidente);

    expect($this->scope->sees($this->alabanza->id))->toBeTrue()
        ->and($this->scope->sees($this->sonido->id))->toBeTrue()
        ->and($this->scope->sees($this->coro->id))->toBeTrue()
        ->and($this->scope->sees($this->damas->id))->toBeFalse();
});

it('un líder de sub-comité ve solo su sub-comité, nada de la rama hermana', function () {
    $lider = User::factory()->create(['site_id' => $this->site->id]);
    CommitteeUser::factory()->forCommittee($this->sonido)->role(CommitteeUser::LEADER)
        ->create(['user_id' => $lider->id]);

    $this->scope->resolve($lider);

    expect($this->scope->committeeIds())->toBe([$this->sonido->id])
        ->and($this->scope->sees($this->coro->id))->toBeFalse()
        ->and($this->scope->sees($this->alabanza->id))->toBeFalse();
});

it('con dos cargos y uno escogido, aplica solo ese ámbito y nunca la unión', function () {
    $hermano = User::factory()->create(['site_id' => $this->site->id]);
    $enAlabanza = CommitteeUser::factory()->forCommittee($this->alabanza)->create(['user_id' => $hermano->id]);
    CommitteeUser::factory()->forCommittee($this->damas)->create(['user_id' => $hermano->id]);

    $this->scope->resolve($hermano, $enAlabanza->id);

    expect($this->scope->sees($this->alabanza->id))->toBeTrue()
        ->and($this->scope->sees($this->damas->id))->toBeFalse()
        ->and($this->scope->canSwitch())->toBeTrue();
});

it('con dos cargos y ninguno escogido, no muestra información', function () {
    $hermano = User::factory()->create(['site_id' => $this->site->id]);
    CommitteeUser::factory()->forCommittee($this->alabanza)->create(['user_id' => $hermano->id]);
    CommitteeUser::factory()->forCommittee($this->damas)->create(['user_id' => $hermano->id]);

    $this->scope->resolve($hermano);

    expect($this->scope->mustChoose())->toBeTrue()
        ->and($this->scope->committeeIds())->toBeEmpty();
});

it('una persona retirada no ve nada', function () {
    $retirado = User::factory()->create(['site_id' => $this->site->id, 'active' => false]);
    CommitteeUser::factory()->forCommittee($this->alabanza)->create(['user_id' => $retirado->id]);

    $this->scope->resolve($retirado);

    expect($this->scope->isActive())->toBeFalse()
        ->and($this->scope->committeeIds())->toBeEmpty();
});
