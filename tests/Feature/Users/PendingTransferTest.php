<?php

use App\Models\Approval;
use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->site = Site::factory()->create();
    $this->pastor = User::factory()->create(['site_id' => $this->site->id, 'is_pastor' => true]);
    $this->alabanza = Committee::factory()->for($this->site)->create();
    $this->sonido = Committee::factory()->for($this->site)->childOf($this->alabanza)->create();
    $this->damas = Committee::factory()->for($this->site)->create();

    $this->saliente = User::factory()->create(['site_id' => $this->site->id]);
    $this->cargo = CommitteeUser::factory()->forCommittee($this->alabanza)->create(['user_id' => $this->saliente->id]);

    $this->entrante = User::factory()->create(['site_id' => $this->site->id]);
});

function relevarPresidencia($prueba): void
{
    $prueba->actingAs($prueba->pastor)->post("/committees/{$prueba->alabanza->id}/roles", [
        'user_id' => $prueba->entrante->id,
        'role' => CommitteeUser::PRESIDENT,
        'handover' => true,
    ]);
}

it('al relevar a la presidencia, sus pendientes guardan de quién venían y cuándo (HU-02, escenario 3)', function () {
    $delComite = Approval::factory()->forCommittee($this->alabanza)->create();
    $delSubComite = Approval::factory()->forCommittee($this->sonido)->create();

    relevarPresidencia($this);

    foreach ([$delComite, $delSubComite] as $solicitud) {
        $solicitud->refresh();

        expect($solicitud->transferred_from_user_id)->toBe($this->saliente->id)
            ->and(Carbon::parse($solicitud->transferred_at)->isToday())->toBeTrue()
            ->and($solicitud->status)->toBe(Approval::PENDING);
    }
});

it('las decisiones ya tomadas no se tocan', function () {
    $resuelta = Approval::factory()->forCommittee($this->alabanza)->resolved()->create();

    relevarPresidencia($this);

    expect($resuelta->fresh()->transferred_from_user_id)->toBeNull();
});

it('los pendientes de otro comité no se tocan', function () {
    $ajena = Approval::factory()->forCommittee($this->damas)->create();

    relevarPresidencia($this);

    expect($ajena->fresh()->transferred_from_user_id)->toBeNull();
});

it('los pendientes que decide el pastor no cambian de manos', function () {
    $delPastor = Approval::factory()->forCommittee($this->alabanza)->create(['level' => Approval::LEVEL_PASTOR]);

    relevarPresidencia($this);

    expect($delPastor->fresh()->transferred_from_user_id)->toBeNull();
});

it('relevar a un líder no marca nada: un líder no decide solicitudes', function () {
    $lider = User::factory()->create(['site_id' => $this->site->id]);
    CommitteeUser::factory()->forCommittee($this->sonido)->role(CommitteeUser::LEADER)->create(['user_id' => $lider->id]);
    $pendiente = Approval::factory()->forCommittee($this->sonido)->create();

    $this->actingAs($this->pastor)->post("/committees/{$this->sonido->id}/roles", [
        'user_id' => $this->entrante->id,
        'role' => CommitteeUser::LEADER,
        'handover' => true,
    ]);

    expect($pendiente->fresh()->transferred_from_user_id)->toBeNull();
});

it('dejar la presidencia vacante también deja constancia en sus pendientes (CL-06)', function () {
    $pendiente = Approval::factory()->forCommittee($this->alabanza)->create();

    $this->actingAs($this->pastor)->delete("/roles/{$this->cargo->id}");

    expect($pendiente->fresh()->transferred_from_user_id)->toBe($this->saliente->id);
});
