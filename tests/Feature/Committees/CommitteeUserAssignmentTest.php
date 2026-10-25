<?php

use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\User;
use Illuminate\Validation\ValidationException;

it('permite a una persona tener dos cargos vigentes a la vez', function () {
    $comite = Committee::factory()->create();
    $otroComite = Committee::factory()->for($comite->site)->create();
    $subComite = Committee::factory()->childOf($otroComite)->create();
    $hermano = User::factory()->create(['site_id' => $comite->site_id]);

    CommitteeUser::factory()->forCommittee($comite)->role(CommitteeUser::PRESIDENT)
        ->create(['user_id' => $hermano->id]);
    CommitteeUser::factory()->forCommittee($subComite)->role(CommitteeUser::LEADER)
        ->create(['user_id' => $hermano->id]);

    expect($hermano->currentRoles()->count())->toBe(2);
});

it('conserva el nombre de quien deja el cargo: el relevo cierra, no borra', function () {
    $comite = Committee::factory()->create();
    $anterior = User::factory()->create(['site_id' => $comite->site_id]);
    $nuevo = User::factory()->create(['site_id' => $comite->site_id]);

    $cargo = CommitteeUser::factory()->forCommittee($comite)->create(['user_id' => $anterior->id]);
    $cargo->update(['ended_at' => now()]);
    CommitteeUser::factory()->forCommittee($comite)->create(['user_id' => $nuevo->id]);

    expect(CommitteeUser::where('committee_id', $comite->id)->count())->toBe(2)
        ->and($cargo->fresh()->isCurrent())->toBeFalse()
        ->and($comite->fresh()->id)->not->toBeNull();
});

it('no deja dos titulares vigentes del mismo cargo', function () {
    $comite = Committee::factory()->create();
    CommitteeUser::factory()->forCommittee($comite)->role(CommitteeUser::PRESIDENT)->create();

    expect(fn () => CommitteeUser::factory()->forCommittee($comite)->role(CommitteeUser::PRESIDENT)->create())
        ->toThrow(ValidationException::class, 'ya tiene titular vigente');
});

it('deja que un comité tenga presidente, secretario y tesorero a la vez', function () {
    $comite = Committee::factory()->create();

    foreach ([CommitteeUser::PRESIDENT, CommitteeUser::SECRETARY, CommitteeUser::TREASURER] as $cargo) {
        CommitteeUser::factory()->forCommittee($comite)->role($cargo)->create();
    }

    expect(CommitteeUser::where('committee_id', $comite->id)->current()->count())->toBe(3);
});

it('no admite secretario ni tesorero en un sub-comité', function () {
    $comite = Committee::factory()->create();
    $subComite = Committee::factory()->childOf($comite)->create();

    expect(fn () => CommitteeUser::factory()->forCommittee($subComite)->role(CommitteeUser::SECRETARY)->create())
        ->toThrow(ValidationException::class, 'cargos de un comité');
});

it('no admite un líder al frente de un comité', function () {
    $comite = Committee::factory()->create();

    expect(fn () => CommitteeUser::factory()->forCommittee($comite)->role(CommitteeUser::LEADER)->create())
        ->toThrow(ValidationException::class, 'lo encabeza su presidente');
});
