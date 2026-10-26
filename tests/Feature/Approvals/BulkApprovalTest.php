<?php

use App\Models\Activity;
use App\Models\Approval;
use App\Support\Approvals\ApprovalFlow;

/*
 * Aprobar varias de una vez (T072, RF-022). Solo el pastor, solo de la misma clase, todas o
 * ninguna, y cada una con su propio registro.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->gastos = fn (int $cuantos) => collect(range(1, $cuantos))
        ->map(fn (int $n) => solicitudDe($this->e['presidente'], Approval::TYPE_EXPENSE, "Gasto {$n}", 100_000 * $n)->id)
        ->all();
});

test('tres gastos se aprueban en una acción, y cada uno queda con su propio registro', function () {
    $ids = ($this->gastos)(3);

    $this->actingAs($this->e['pastor'])->post('/approvals/bulk-approve', ['ids' => $ids])
        ->assertSessionHas('notice', 'Listo. Quedaron aprobadas las 3 solicitudes, cada una con su propio registro.');

    $aprobadas = Approval::whereKey($ids)->get();

    expect($aprobadas->pluck('status')->unique()->all())->toBe([Approval::APPROVED])
        ->and($aprobadas->pluck('resolved_by_user_id')->unique()->all())->toBe([$this->e['pastor']->id])
        ->and($aprobadas->whereNotNull('resolved_at'))->toHaveCount(3);
});

test('las actividades aprobadas en lote entran como aprobadas, igual que una por una', function () {
    $ids = [solicitudDe($this->e['presidente'], titulo: 'Culto unido')->id, solicitudDe($this->e['presidentaDamas'], titulo: 'Retiro de damas')->id];

    $this->actingAs($this->e['pastor'])->post('/approvals/bulk-approve', ['ids' => $ids]);

    expect(Activity::whereIn('title', ['Culto unido', 'Retiro de damas'])->pluck('status')->unique()->all())->toBe([Activity::APPROVED]);
});

test('solo se aprueban juntas las de la misma clase', function () {
    $ids = [($this->gastos)(1)[0], solicitudDe($this->e['presidente'])->id];

    $this->actingAs($this->e['pastor'])->from('/approvals')->post('/approvals/bulk-approve', ['ids' => $ids])
        ->assertSessionHasErrors(['approval' => 'Solo se aprueban juntas las solicitudes de la misma clase.']);

    expect(Approval::whereKey($ids)->pluck('status')->unique()->all())->toBe([Approval::PENDING]);
});

test('si una ya la había resuelto alguien, no se aprueba ninguna y se explica', function () {
    $ids = ($this->gastos)(2);
    app(ApprovalFlow::class)->reject(Approval::find($ids[0]), $this->e['pastor'], 'Ya se compró con otra plata.');

    $this->actingAs($this->e['pastor'])->from('/approvals')->post('/approvals/bulk-approve', ['ids' => $ids])
        ->assertSessionHasErrors('approval');

    expect(Approval::find($ids[1])->status)->toBe(Approval::PENDING);
});

test('lo que todavía está en manos del presidente no entra en el lote del pastor', function () {
    $ids = [solicitudDe($this->e['liderSonido'], Approval::TYPE_EXPENSE, 'Cables', 50_000)->id, ($this->gastos)(1)[0]];

    $this->actingAs($this->e['pastor'])->post('/approvals/bulk-approve', ['ids' => $ids])->assertForbidden();

    expect(Approval::whereKey($ids)->pluck('status')->unique()->all())->toBe([Approval::PENDING]);
});

test('nadie más que el pastor aprueba en lote', function (string $quien) {
    $ids = [solicitudDe($this->e['liderSonido'])->id, solicitudDe($this->e['liderCoro'])->id];

    $this->actingAs($this->e[$quien])->post('/approvals/bulk-approve', ['ids' => $ids])->assertForbidden();

    expect(Approval::whereKey($ids)->pluck('status')->unique()->all())->toBe([Approval::PENDING]);
})->with(['presidente', 'liderSonido']);

test('para una sola, se usa el botón de la tarjeta', function () {
    $this->actingAs($this->e['pastor'])->from('/approvals')->post('/approvals/bulk-approve', ['ids' => ($this->gastos)(1)])
        ->assertSessionHasErrors(['ids' => 'Para aprobar de a una, use el botón de cada tarjeta.']);
});
