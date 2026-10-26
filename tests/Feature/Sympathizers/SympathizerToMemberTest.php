<?php

use App\Models\Approval;
use App\Models\Member;
use App\Models\Sympathizer;
use Illuminate\Support\Carbon;

/*
 * El paso a miembro (T118; HU-14 escenario 5; RF-019, RF-045, RN-06): lo propone quien preside
 * Evangelismo, le llega al pastor, y solo con su aprobacion la persona entra a la lista. La ficha de
 * simpatizante no se borra (CL-10).
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->ev = evangelismoDePrueba($this->e);
    $this->persona = simpatizanteDePrueba($this->ev, ['name' => 'Edwin Alexis Toro', 'gender' => 'm'], 5);
});

test('5. la propuesta le llega al pastor, y solo con su aprobación la persona entra a la lista de miembros', function () {
    $this->actingAs($this->ev['presidenteEvangelismo'])->post("/sympathizers/{$this->persona->id}/promote")
        ->assertSessionHas('notice', 'Listo. La solicitud para que Edwin Alexis Toro pase a ser miembro le llegó al pastor.');

    $solicitud = Approval::withoutGlobalScopes()->sole();

    expect($solicitud)->type->toBe(Approval::TYPE_NEW_MEMBER)->level->toBe(Approval::LEVEL_PASTOR)
        ->and(Member::withoutGlobalScopes()->count())->toBe(0);

    $this->actingAs($this->e['pastor'])->post("/approvals/{$solicitud->id}/approve")->assertSessionHas('notice', 'Listo. La solicitud quedó aprobada.');

    $miembro = Member::withoutGlobalScopes()->sole();
    $bautismo = $this->persona->stages()->where('stage', Sympathizer::BAPTISM_STAGE)->value('reached_at');

    expect($miembro)
        ->name->toBe('Edwin Alexis Toro')
        ->baptized->toBeTrue()
        ->profile_pending->toBeTrue()
        ->and($miembro->baptized_at->toDateString())->toBe(Carbon::parse($bautismo)->toDateString())
        ->and(Sympathizer::withoutGlobalScopes()->find($this->persona->id))
        ->converted_member_id->toBe($miembro->id)
        ->first_contact_at->toDateString()->toBe($this->persona->first_contact_at->toDateString());
});

test('si el pastor no la aprueba, la persona no entra a la lista', function () {
    $this->actingAs($this->ev['presidenteEvangelismo'])->post("/sympathizers/{$this->persona->id}/promote");
    $solicitud = Approval::withoutGlobalScopes()->sole();

    $this->actingAs($this->e['pastor'])->post("/approvals/{$solicitud->id}/reject", ['reason' => 'Falta conversar con él.']);

    expect(Member::withoutGlobalScopes()->count())->toBe(0);
});

test('antes de recorrer las cinco etapas no se puede proponer, ni dos veces', function () {
    $nuevo = simpatizanteDePrueba($this->ev, [], 4);

    $this->actingAs($this->ev['presidenteEvangelismo'])->post("/sympathizers/{$nuevo->id}/promote")->assertForbidden();

    $this->actingAs($this->ev['presidenteEvangelismo'])->post("/sympathizers/{$this->persona->id}/promote");
    $this->actingAs($this->ev['presidenteEvangelismo'])->from("/sympathizers/{$this->persona->id}")->post("/sympathizers/{$this->persona->id}/promote")
        ->assertSessionHasErrors(['promote' => 'Ya hay una solicitud esperando al pastor para que pase a ser miembro.']);

    expect(Approval::withoutGlobalScopes()->count())->toBe(1);
});
