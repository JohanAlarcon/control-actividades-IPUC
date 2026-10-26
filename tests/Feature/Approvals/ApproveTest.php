<?php

use App\Models\Activity;
use App\Models\Approval;
use App\Support\Approvals\ApprovalFlow;

/* Aprobar (T069): aplica ApprovalFlow y el efecto sobre la actividad. */

beforeEach(function () {
    $this->e = estructuraDePrueba();
});

test('cuando el pastor aprueba, la actividad queda aprobada en el cronograma con su nombre y la fecha', function () {
    $solicitud = solicitudDe($this->e['presidente'], titulo: 'Convención juvenil');

    $this->actingAs($this->e['pastor'])->post("/approvals/{$solicitud->id}/approve")
        ->assertRedirect()
        ->assertSessionHas('notice', 'Listo. La solicitud quedó aprobada.');

    $actividad = $solicitud->approvable->fresh();

    expect($actividad->status)->toBe(Activity::APPROVED)
        ->and($actividad->approved_by_user_id)->toBe($this->e['pastor']->id)
        ->and($actividad->approved_at)->not->toBeNull();
});

test('cuando el presidente da su visto bueno, se le dice que ahora pasa al pastor, que es quien aprueba', function () {
    $solicitud = solicitudDe($this->e['liderSonido']);

    $this->actingAs($this->e['presidente'])->post("/approvals/{$solicitud->id}/approve")
        ->assertSessionHas('notice', 'Listo. Le dio su visto bueno, y ahora pasa al pastor, que es quien aprueba.');

    expect($solicitud->approvable->fresh()->status)->toBe(Activity::PENDING);
});

test('lo ya resuelto no se vuelve a aprobar: se responde con una explicación, no con un error', function () {
    $solicitud = solicitudDe($this->e['presidente']);
    app(ApprovalFlow::class)->reject($solicitud, $this->e['pastor'], 'Ese mes ya hay vigilia.');

    $this->actingAs($this->e['pastor'])->from('/approvals')->post("/approvals/{$solicitud->id}/approve")
        ->assertRedirect('/approvals')
        ->assertSessionHasErrors('approval');

    expect($solicitud->fresh()->status)->toBe(Approval::REJECTED);
});
