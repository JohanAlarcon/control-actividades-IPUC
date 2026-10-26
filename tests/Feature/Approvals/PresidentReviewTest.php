<?php

use App\Models\Approval;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Los cuatro escenarios de HU-06 (T071): el presidente revisa lo que proponen sus sub-comites.
 * Cada uno entra por la pantalla, como lo haria el presidente.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
});

test('1. un gasto, una tarea o un cambio de fecha queda resuelto al aprobarlo, y el líder ve quién y cuándo', function (string $tipo) {
    $solicitud = solicitudDe($this->e['liderSonido'], $tipo, 'Lo que pide Sonido', $tipo === 'expense' ? 300_000 : null);

    $this->actingAs($this->e['presidente'])->post("/approvals/{$solicitud->id}/approve");

    expect($solicitud->fresh()->status)->toBe(Approval::APPROVED);

    $this->actingAs($this->e['liderSonido'])->get('/approvals')
        ->assertInertia(fn (Assert $page) => $page
            ->where('history.0.status', 'approved')
            ->where('history.0.resolvedBy', 'Hermano Rigoberto Páez')
            ->where('history.0.resolvedAt', fn ($fecha) => $fecha !== null));
})->with(['gasto' => 'expense', 'tarea' => 'task', 'cambio de fecha' => 'date_change']);

test('2. una actividad nueva aprobada por el presidente no se cierra: pasa al pastor, y quien la propuso lo ve', function () {
    $solicitud = solicitudDe($this->e['liderSonido'], titulo: 'Vigilia de jóvenes');

    $this->actingAs($this->e['presidente'])->post("/approvals/{$solicitud->id}/approve");

    expect($solicitud->fresh())
        ->status->toBe(Approval::PENDING)
        ->level->toBe(Approval::LEVEL_PASTOR);

    $this->actingAs($this->e['liderSonido'])->get('/approvals')
        ->assertInertia(fn (Assert $page) => $page
            ->where('escalated.0.title', 'Vigilia de jóvenes')
            ->where('escalated.0.level', 'pastor')
            ->where('escalated.0.escalatedBy', 'Hermano Rigoberto Páez'));
});

test('3. rechazar exige el motivo, y el motivo queda a la vista de quien propuso', function () {
    $solicitud = solicitudDe($this->e['liderMontaje']);

    $this->actingAs($this->e['presidente'])->post("/approvals/{$solicitud->id}/reject")->assertSessionHasErrors('reason');
    $this->actingAs($this->e['presidente'])->post("/approvals/{$solicitud->id}/reject", ['reason' => 'Falta el permiso del templo.']);

    $this->actingAs($this->e['liderMontaje'])->get('/approvals')
        ->assertInertia(fn (Assert $page) => $page->where('history.0.reason', 'Falta el permiso del templo.'));
});

test('4. al abrir el inicio, el presidente ve cuántas solicitudes esperan y desde cuándo', function () {
    $this->travelTo(now()->subDays(6), fn () => solicitudDe($this->e['liderSonido']));
    $this->travelTo(now()->subDays(2), fn () => solicitudDe($this->e['liderCoro']));
    solicitudDe($this->e['liderMontaje']);

    $respuesta = $this->actingAs($this->e['presidente'])
        ->withHeaders(pedidoParcial('pendingCount,pendingSince'))
        ->get('/dashboard');

    expect($respuesta->json('props.pendingCount'))->toBe(3)
        ->and(now()->parse($respuesta->json('props.pendingSince'))->isSameDay(now()->subDays(6)))->toBeTrue();
});
