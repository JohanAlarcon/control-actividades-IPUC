<?php

use App\Models\Activity;
use App\Models\Approval;
use App\Support\Approvals\ApprovalFlow;
use Illuminate\Validation\ValidationException;

/*
 * La cadena de escalamiento (data-model §5). Es la regla que mas se ha movido en el proyecto:
 * a quien le llega cada solicitud, cuando una aprobacion cierra y cuando sube al pastor.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->flujo = new ApprovalFlow;
});

test('lo que pide un líder o un secretario le llega al presidente; lo del presidente, al pastor', function () {
    expect(solicitudDe($this->e['liderMontaje'])->level)->toBe(Approval::LEVEL_PRESIDENT)
        ->and(solicitudDe($this->e['secretaria'])->level)->toBe(Approval::LEVEL_PRESIDENT)
        ->and(solicitudDe($this->e['presidente'])->level)->toBe(Approval::LEVEL_PASTOR);
});

test('una solicitud recién creada ya se sabe pendiente, sin volver a leerla', function () {
    $solicitud = solicitudDe($this->e['liderSonido']);

    expect($solicitud->isPending())->toBeTrue();

    $solicitud->update(['detail' => 'Con el equipo nuevo.']);

    expect($solicitud->fresh()->detail)->toBe('Con el equipo nuevo.');
});

test('el presidente aprueba una actividad y la solicitud sigue pendiente, ahora en el pastor', function () {
    $solicitud = $this->flujo->approve(solicitudDe($this->e['liderSonido']), $this->e['presidente']);

    expect($solicitud->status)->toBe(Approval::PENDING)
        ->and($solicitud->level)->toBe(Approval::LEVEL_PASTOR)
        ->and($solicitud->escalated_by_user_id)->toBe($this->e['presidente']->id)
        ->and($solicitud->escalated_at)->not->toBeNull()
        ->and($solicitud->approvable->status)->toBe(Activity::PENDING);
});

test('el pastor la cierra: la actividad queda aprobada con su nombre y la fecha', function () {
    $solicitud = $this->flujo->approve(solicitudDe($this->e['liderSonido']), $this->e['presidente']);
    $this->flujo->approve($solicitud, $this->e['pastor']);

    $actividad = $solicitud->approvable->fresh();

    expect($solicitud->fresh()->status)->toBe(Approval::APPROVED)
        ->and($actividad->status)->toBe(Activity::APPROVED)
        ->and($actividad->approved_by_user_id)->toBe($this->e['pastor']->id)
        ->and($actividad->approved_at)->not->toBeNull();
});

test('un gasto de un líder, de cualquier monto, lo cierra el presidente en ese paso', function () {
    $gasto = solicitudDe($this->e['liderSonido'], Approval::TYPE_EXPENSE, 'Consola de sonido', 9_800_000);

    $solicitud = $this->flujo->approve($gasto, $this->e['presidente']);

    expect($solicitud->status)->toBe(Approval::APPROVED)
        ->and($solicitud->level)->toBe(Approval::LEVEL_PRESIDENT)
        ->and($solicitud->resolved_by_user_id)->toBe($this->e['presidente']->id);
});

test('el gasto del propio comité del presidente no crea solicitud', function () {
    expect(solicitudDe($this->e['presidente'], Approval::TYPE_EXPENSE, 'Cuerdas para el bajo', 120_000))->toBeNull()
        ->and(Approval::count())->toBe(0);
});

test('un gasto en manos del pastor no se guarda, venga de donde venga', function () {
    Approval::factory()->forCommittee($this->e['alabanza'])->create(['type' => Approval::TYPE_EXPENSE, 'level' => Approval::LEVEL_PASTOR]);
})->throws(RuntimeException::class, 'Un gasto no llega al pastor');

test('rechazar cierra en cualquier nivel y deja el motivo en la actividad', function () {
    $solicitud = $this->flujo->reject(solicitudDe($this->e['liderCoro']), $this->e['presidente'], 'Ese sábado hay convención.');

    expect($solicitud->status)->toBe(Approval::REJECTED)
        ->and($solicitud->approvable->status)->toBe(Activity::REJECTED)
        ->and($solicitud->approvable->rejection_reason)->toBe('Ese sábado hay convención.');
});

test('un rechazo sin motivo no se guarda', function () {
    $this->flujo->reject(solicitudDe($this->e['liderCoro']), $this->e['presidente'], '   ');
})->throws(RuntimeException::class, 'Un rechazo sin motivo');

test('lo resuelto no se vuelve a resolver, y se explica quién lo resolvió', function () {
    $solicitud = solicitudDe($this->e['presidente']);
    $this->flujo->reject($solicitud, $this->e['pastor'], 'Se cruza con la Santa Cena.');

    expect(fn () => $this->flujo->approve($solicitud, $this->e['pastor']))
        ->toThrow(ValidationException::class, "ya la resolvió {$this->e['pastor']->name}");
});

test('un doble toque del presidente no aprueba a nombre del pastor', function () {
    $solicitud = solicitudDe($this->e['liderSonido']);
    $mismaPantalla = Approval::find($solicitud->id);

    $this->flujo->approve($solicitud, $this->e['presidente']);

    expect(fn () => $this->flujo->approve($mismaPantalla, $this->e['presidente']))
        ->toThrow(ValidationException::class, 'ya pasó a la revisión del pastor');
    expect($solicitud->fresh()->status)->toBe(Approval::PENDING);
});
