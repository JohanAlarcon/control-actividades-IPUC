<?php

use App\Models\Approval;
use Inertia\Testing\AssertableInertia as Assert;

/* Rechazar (T070): el motivo es obligatorio y lo lee quien propuso (RF-020). */

beforeEach(function () {
    $this->e = estructuraDePrueba();
});

test('sin motivo no se rechaza', function (string $motivo) {
    $solicitud = solicitudDe($this->e['liderSonido']);

    $this->actingAs($this->e['presidente'])->from('/approvals')->post("/approvals/{$solicitud->id}/reject", ['reason' => $motivo])
        ->assertRedirect('/approvals')
        ->assertSessionHasErrors('reason');

    expect($solicitud->fresh()->status)->toBe(Approval::PENDING);
})->with(['vacío' => '', 'solo espacios' => '    ', 'muy corto' => 'no']);

test('el motivo le aparece a quien propuso, con el nombre de quien decidió', function () {
    $solicitud = solicitudDe($this->e['liderSonido'], titulo: 'Jornada de sonido');

    $this->actingAs($this->e['presidente'])->post("/approvals/{$solicitud->id}/reject", ['reason' => 'Ese sábado es la Santa Cena.'])
        ->assertSessionHas('notice');

    expect($solicitud->approvable->fresh()->rejection_reason)->toBe('Ese sábado es la Santa Cena.');

    $this->actingAs($this->e['liderSonido'])->get('/approvals')
        ->assertInertia(fn (Assert $page) => $page
            ->where('history.0.title', 'Jornada de sonido')
            ->where('history.0.status', 'rejected')
            ->where('history.0.reason', 'Ese sábado es la Santa Cena.')
            ->where('history.0.resolvedBy', 'Hermano Rigoberto Páez'));
});
