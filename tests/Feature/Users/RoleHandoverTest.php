<?php

use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\Site;
use App\Models\User;

beforeEach(function () {
    $this->site = Site::factory()->create();
    $this->pastor = User::factory()->create(['site_id' => $this->site->id, 'is_pastor' => true]);
    $this->alabanza = Committee::factory()->for($this->site)->create();

    $this->saliente = User::factory()->create(['site_id' => $this->site->id, 'name' => 'Hermano Saliente']);
    $this->cargoViejo = CommitteeUser::factory()->forCommittee($this->alabanza)
        ->create(['user_id' => $this->saliente->id]);

    $this->entrante = User::factory()->create(['site_id' => $this->site->id]);
});

it('el relevo cierra el cargo anterior y abre uno nuevo: quedan dos filas', function () {
    $this->actingAs($this->pastor)
        ->post("/committees/{$this->alabanza->id}/roles", ['user_id' => $this->entrante->id, 'role' => CommitteeUser::PRESIDENT, 'handover' => true])
        ->assertSessionHas('notice', 'El cargo quedó asignado.');

    $filas = CommitteeUser::where('committee_id', $this->alabanza->id)
        ->where('role', CommitteeUser::PRESIDENT)
        ->get();

    expect($filas)->toHaveCount(2)
        ->and($filas->whereNull('ended_at')->first()->user_id)->toBe($this->entrante->id)
        ->and($this->cargoViejo->fresh()->ended_at->isToday())->toBeTrue();
});

it('la historia conserva el nombre de quien tuvo el cargo', function () {
    $this->actingAs($this->pastor)
        ->post("/committees/{$this->alabanza->id}/roles", ['user_id' => $this->entrante->id, 'role' => CommitteeUser::PRESIDENT, 'handover' => true]);

    $anterior = CommitteeUser::with('user')->find($this->cargoViejo->id);

    expect($anterior->user->name)->toBe('Hermano Saliente');
});

it('quien fue relevado deja de tener ámbito en ese comité', function () {
    $this->actingAs($this->pastor)
        ->post("/committees/{$this->alabanza->id}/roles", ['user_id' => $this->entrante->id, 'role' => CommitteeUser::PRESIDENT, 'handover' => true]);

    expect($this->saliente->currentRoles()->count())->toBe(0);

    $this->actingAs($this->saliente->fresh())->get('/dashboard')
        ->assertInertia(fn ($page) => $page->where('scope.activeRole', null));
});

it('dejar un cargo vacante lo cierra sin borrarlo', function () {
    $this->actingAs($this->pastor)->delete("/roles/{$this->cargoViejo->id}")
        ->assertSessionHas('notice');

    $cargo = CommitteeUser::find($this->cargoViejo->id);

    expect($cargo)->not->toBeNull()
        ->and($cargo->ended_at)->not->toBeNull()
        ->and($this->saliente->currentRoles()->count())->toBe(0);
});

it('un cargo de comité no se asigna en un sub-comité', function () {
    $sonido = Committee::factory()->for($this->site)->childOf($this->alabanza)->create();

    $this->actingAs($this->pastor)
        ->post("/committees/{$sonido->id}/roles", ['user_id' => $this->entrante->id, 'role' => CommitteeUser::PRESIDENT])
        ->assertSessionHasErrors('role');

    expect(CommitteeUser::where('committee_id', $sonido->id)->exists())->toBeFalse();
});

it('no se puede asignar un cargo a alguien de otra sede', function () {
    $deOtraSede = User::factory()->create(['site_id' => Site::factory()->create()->id]);

    $this->actingAs($this->pastor)
        ->post("/committees/{$this->alabanza->id}/roles", ['user_id' => $deOtraSede->id, 'role' => CommitteeUser::PRESIDENT, 'handover' => true])
        ->assertNotFound();

    expect($this->cargoViejo->fresh()->ended_at)->toBeNull();
});

it('asignar un cargo ocupado sin confirmar no releva a nadie y dice quién lo tiene', function () {
    $this->actingAs($this->pastor)
        ->post("/committees/{$this->alabanza->id}/roles", ['user_id' => $this->entrante->id, 'role' => CommitteeUser::PRESIDENT])
        ->assertSessionHasErrors('handover');

    expect(session('errors')->first('handover'))->toContain('Hermano Saliente')
        ->and($this->cargoViejo->fresh()->ended_at)->toBeNull()
        ->and($this->entrante->currentRoles()->count())->toBe(0);
});

it('asignarle a alguien el cargo que ya tiene se rechaza', function () {
    $this->actingAs($this->pastor)
        ->post("/committees/{$this->alabanza->id}/roles", ['user_id' => $this->saliente->id, 'role' => CommitteeUser::PRESIDENT, 'handover' => true])
        ->assertSessionHasErrors(['role' => 'Esa persona ya tiene ese cargo.']);

    expect(CommitteeUser::where('committee_id', $this->alabanza->id)->count())->toBe(1);
});

it('crear una cuenta con un cargo ocupado pide confirmar antes de crear nada', function () {
    $this->actingAs($this->pastor)->post('/users', [
        'name' => 'Hermano Nuevo Presidente',
        'committee_id' => $this->alabanza->id,
        'role' => CommitteeUser::PRESIDENT,
    ])->assertSessionHasErrors('handover');

    expect(User::where('name', 'Hermano Nuevo Presidente')->exists())->toBeFalse()
        ->and($this->cargoViejo->fresh()->ended_at)->toBeNull();
});
