<?php

use App\Models\Approval;

/*
 * Quien resuelve cada solicitud (T067). Se entra por la ruta, como lo haria la persona: la
 * policy, el filtro de alcance y el registro de la ruta trabajan juntos.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
});

test('el presidente resuelve lo que le piden sus sub-comités, en cualquier nivel', function () {
    $solicitud = solicitudDe($this->e['liderMontaje']);

    $this->actingAs($this->e['presidente'])->post("/approvals/{$solicitud->id}/approve")->assertRedirect();

    expect($solicitud->fresh()->escalated_by_user_id)->toBe($this->e['presidente']->id);
});

test('un presidente no resuelve lo que está en manos del pastor', function () {
    $solicitud = solicitudDe($this->e['presidente']);

    $this->actingAs($this->e['presidente'])->post("/approvals/{$solicitud->id}/approve")->assertForbidden();
    $this->actingAs($this->e['presidente'])->post("/approvals/{$solicitud->id}/reject", ['reason' => 'Mejor la cambio.'])->assertForbidden();

    expect($solicitud->fresh()->status)->toBe(Approval::PENDING);
});

test('un presidente no resuelve lo de otro comité: para él, esa solicitud no existe', function () {
    $solicitud = solicitudDe($this->e['liderCoro']);

    $this->actingAs($this->e['presidentaDamas'])->post("/approvals/{$solicitud->id}/approve")->assertNotFound();

    expect($solicitud->fresh()->level)->toBe(Approval::LEVEL_PRESIDENT);
});

test('el pastor no decide a nombre de un presidente', function () {
    $solicitud = solicitudDe($this->e['liderSonido']);

    $this->actingAs($this->e['pastor'])->post("/approvals/{$solicitud->id}/approve")->assertForbidden();

    expect($solicitud->fresh()->level)->toBe(Approval::LEVEL_PRESIDENT);
});

test('un líder o una secretaria no resuelven, ni siquiera lo de su propio comité', function () {
    $solicitud = solicitudDe($this->e['liderMontaje']);

    $this->actingAs($this->e['liderSonido'])->post("/approvals/{$solicitud->id}/approve")->assertForbidden();
    $this->actingAs($this->e['secretaria'])->post("/approvals/{$solicitud->id}/reject", ['reason' => 'No me parece.'])->assertForbidden();

    expect($solicitud->fresh()->status)->toBe(Approval::PENDING);
});
