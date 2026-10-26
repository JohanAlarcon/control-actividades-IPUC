<?php

use App\Models\Approval;

it('deja cambiar una solicitud que sigue pendiente', function () {
    $solicitud = Approval::factory()->create(['level' => Approval::LEVEL_PASTOR]);

    $solicitud->update(['status' => Approval::APPROVED, 'resolved_at' => now()]);

    expect($solicitud->fresh()->status)->toBe(Approval::APPROVED);
});

it('no deja editar una solicitud ya aprobada, y lo explica', function () {
    $solicitud = Approval::factory()->resolved()->create();

    expect(fn () => $solicitud->update(['title' => 'Otra cosa']))
        ->toThrow(RuntimeException::class, 'registrar una decisión nueva');
});

it('no deja editar una solicitud ya rechazada', function () {
    $solicitud = Approval::factory()->resolved(Approval::REJECTED)->create();

    expect(fn () => $solicitud->update(['reason' => 'Otro motivo']))
        ->toThrow(RuntimeException::class);
});

it('no deja borrar ninguna decisión', function () {
    $solicitud = Approval::factory()->resolved()->create();

    expect(fn () => $solicitud->delete())
        ->toThrow(RuntimeException::class, 'quedan en la historia');
});

it('guarda el motivo visible cuando se rechaza', function () {
    $solicitud = Approval::factory()->resolved(Approval::REJECTED)->create();

    expect($solicitud->reason)->not->toBeEmpty()
        ->and($solicitud->isPending())->toBeFalse();
});
