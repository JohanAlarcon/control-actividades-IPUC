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

test('1. un gasto, una tarea o un cambio de fecha con el visto bueno del presidente no queda resuelto: pasa al pastor, y el líder lo ve', function (string $tipo) {
    $solicitud = solicitudDe($this->e['liderSonido'], $tipo, 'Lo que pide Sonido', $tipo === 'expense' ? 300_000 : null);

    $this->actingAs($this->e['presidente'])->post("/approvals/{$solicitud->id}/approve");

    expect($solicitud->fresh())
        ->status->toBe(Approval::PENDING)
        ->level->toBe(Approval::LEVEL_PASTOR);

    $this->actingAs($this->e['liderSonido'])->get('/approvals')
        ->assertInertia(fn (Assert $page) => $page
            ->where('escalated.0.title', 'Lo que pide Sonido')
            ->where('escalated.0.level', 'pastor')
            ->where('escalated.0.escalatedBy', 'Hermano Rigoberto Páez'));
})->with(['gasto' => 'expense', 'tarea' => 'task', 'cambio de fecha' => 'date_change']);

test('2. una actividad nueva con el visto bueno del presidente no se cierra: pasa al pastor, y quien la propuso lo ve', function () {
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

test('3. si no está de acuerdo, la devuelve: se exige el motivo, quien la propuso lo ve y no llega al pastor', function () {
    $solicitud = solicitudDe($this->e['liderCoro']);

    $this->actingAs($this->e['presidente'])->post("/approvals/{$solicitud->id}/reject")->assertSessionHasErrors('reason');
    $this->actingAs($this->e['presidente'])->post("/approvals/{$solicitud->id}/reject", ['reason' => 'Falta el permiso del templo.']);

    $this->actingAs($this->e['liderCoro'])->get('/approvals')
        ->assertInertia(fn (Assert $page) => $page->where('history.0.reason', 'Falta el permiso del templo.'));

    $this->actingAs($this->e['pastor'])->get('/approvals')
        ->assertInertia(fn (Assert $page) => $page->where('counts.inbox', 0));
});

test('4. al abrir el inicio, el presidente ve cuántas solicitudes esperan y desde cuándo', function () {
    $this->travelTo(now()->subDays(6), fn () => solicitudDe($this->e['liderSonido']));
    $this->travelTo(now()->subDays(2), fn () => solicitudDe($this->e['liderCoro']));
    solicitudDe($this->e['secretaria']);

    $respuesta = $this->actingAs($this->e['presidente'])
        ->withHeaders(pedidoParcial('pendingCount,pendingSince'))
        ->get('/dashboard');

    expect($respuesta->json('props.pendingCount'))->toBe(3)
        ->and(now()->parse($respuesta->json('props.pendingSince'))->isSameDay(now()->subDays(6)))->toBeTrue();
});
