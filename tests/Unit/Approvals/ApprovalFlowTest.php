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

test('a quién le llega: lo del líder, al presidente del comité de arriba; lo del presidente, al pastor', function () {
    $cargoDe = fn ($persona) => App\Models\CommitteeUser::where('user_id', $persona->id)->sole();

    expect($this->flujo->recipientFor($cargoDe($this->e['liderMontaje']))?->id)->toBe($this->e['presidente']->id)
        ->and($this->flujo->recipientFor($cargoDe($this->e['liderCoro']))?->id)->toBe($this->e['presidente']->id)
        ->and($this->flujo->recipientFor($cargoDe($this->e['secretaria']))?->id)->toBe($this->e['presidente']->id)
        ->and($this->flujo->recipientFor($cargoDe($this->e['presidente']))?->id)->toBe($this->e['pastor']->id);
});

test('si la presidencia está vacante o sin acceso, no le llega a nadie todavía', function () {
    $lider = App\Models\CommitteeUser::where('user_id', $this->e['liderSonido']->id)->sole();

    $this->e['presidente']->update(['active' => false]);
    expect($this->flujo->recipientFor($lider))->toBeNull();

    $this->e['presidente']->update(['active' => true]);
    App\Models\CommitteeUser::where('user_id', $this->e['presidente']->id)->sole()->update(['ended_at' => now()]);
    expect($this->flujo->recipientFor($lider))->toBeNull();
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

test('el visto bueno del presidente nunca cierra: un gasto, una tarea o un cambio de fecha también suben al pastor', function (string $tipo) {
    $pedido = solicitudDe($this->e['liderSonido'], $tipo, 'Lo que pide Sonido', $tipo === Approval::TYPE_EXPENSE ? 9_800_000 : null);

    $solicitud = $this->flujo->approve($pedido, $this->e['presidente']);

    expect($solicitud->status)->toBe(Approval::PENDING)
        ->and($solicitud->level)->toBe(Approval::LEVEL_PASTOR)
        ->and($solicitud->escalated_by_user_id)->toBe($this->e['presidente']->id);
})->with(['gasto' => Approval::TYPE_EXPENSE, 'tarea' => Approval::TYPE_TASK, 'cambio de fecha' => Approval::TYPE_DATE_CHANGE]);

test('el gasto del propio comité del presidente también lo aprueba el pastor', function () {
    expect(solicitudDe($this->e['presidente'], Approval::TYPE_EXPENSE, 'Cuerdas para el bajo', 120_000)->level)->toBe(Approval::LEVEL_PASTOR);
});

test('una solicitud no queda aprobada en manos del presidente, venga de donde venga', function () {
    Approval::factory()->forCommittee($this->e['alabanza'])->create(['status' => Approval::APPROVED, 'level' => Approval::LEVEL_PRESIDENT, 'resolved_at' => now()]);
})->throws(RuntimeException::class, 'Solo el pastor aprueba');

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
