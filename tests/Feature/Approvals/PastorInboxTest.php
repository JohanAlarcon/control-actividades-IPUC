<?php

use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\Approval;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Los cuatro escenarios de HU-07, desde el pastor (T073). La propuesta entra por el formulario y
 * pasa por el visto bueno del presidente, como en la vida real.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-03-10 10:00', 'America/Bogota'));

    $this->proponerYRevisar = function (string $titulo): Approval {
        $this->actingAs($this->e['liderSonido'])->post('/activities', [
            'title' => $titulo,
            'activity_type_id' => ActivityType::withoutGlobalScopes()->where('site_id', $this->e['sede']->id)->where('name', 'Culto')->value('id'),
            'date' => '2026-04-18',
            'time' => '19:00',
            'location' => 'Templo central',
            'budget' => '350.000',
            'confirm_conflict' => true,
        ]);
        $solicitud = Approval::withoutGlobalScopes()->where('title', $titulo)->sole();
        $this->actingAs($this->e['presidente'])->post("/approvals/{$solicitud->id}/approve");

        return $solicitud->fresh();
    };
});

test('1. la bandeja dice de qué comité viene, quién la pidió, por quién pasó, el presupuesto y el efecto en el cronograma', function () {
    $solicitud = ($this->proponerYRevisar)('Vigilia de jóvenes');
    $this->travel(1)->minutes();
    solicitudDe($this->e['presidente'], Approval::TYPE_EXPENSE, 'Sillas para el templo', 1_200_000);

    $this->actingAs($this->e['pastor'])->get('/approvals')
        ->assertInertia(fn (Assert $page) => $page
            ->where('counts.inbox', 2)
            ->where('inbox.0.id', $solicitud->id)
            ->where('inbox.0.committee', 'Sub-comité de Sonido')
            ->where('inbox.0.requestedBy', 'Hermano Andrés Molina')
            ->where('inbox.0.escalatedBy', 'Hermano Rigoberto Páez')
            ->where('inbox.0.amount', fn ($monto) => (float) $monto === 350000.0)
            ->where('inbox.0.activity.location', 'Templo central')
            ->where('inbox.0.activity.date', fn (string $fecha) => Carbon::parse($fecha)->setTimezone('America/Bogota')->format('Y-m-d H:i') === '2026-04-18 19:00')
            ->where('inbox.1.type', 'expense')
            ->where('inbox.1.amount', fn ($monto) => (float) $monto === 1200000.0));
});

test('2. al aprobarla, la actividad entra al cronograma como aprobada, con el nombre del pastor y la fecha', function () {
    $solicitud = ($this->proponerYRevisar)('Vigilia de jóvenes');

    $this->actingAs($this->e['pastor'])->post("/approvals/{$solicitud->id}/approve")
        ->assertSessionHas('notice', 'Listo. La solicitud quedó aprobada.');

    expect(Activity::withoutGlobalScopes()->where('title', 'Vigilia de jóvenes')->sole())
        ->status->toBe(Activity::APPROVED)
        ->approved_by_user_id->toBe($this->e['pastor']->id)
        ->approved_at->not->toBeNull();

    $this->actingAs($this->e['liderSonido'])->get('/approvals')
        ->assertInertia(fn (Assert $page) => $page->where('history.0.status', 'approved')->where('history.0.resolvedBy', $this->e['pastor']->name));
});

test('3. si no la aprueba, escribe el motivo: la actividad queda como no aprobada y el comité ve por qué', function () {
    $solicitud = ($this->proponerYRevisar)('Vigilia de jóvenes');

    $this->actingAs($this->e['pastor'])->post("/approvals/{$solicitud->id}/reject")->assertSessionHasErrors('reason');
    $this->actingAs($this->e['pastor'])->post("/approvals/{$solicitud->id}/reject", ['reason' => 'Ese mes ya hay convención.']);

    expect(Activity::withoutGlobalScopes()->where('title', 'Vigilia de jóvenes')->sole())
        ->status->toBe(Activity::REJECTED)
        ->rejection_reason->toBe('Ese mes ya hay convención.');

    $this->actingAs($this->e['liderSonido'])->get('/approvals')
        ->assertInertia(fn (Assert $page) => $page
            ->where('history.0.status', 'rejected')
            ->where('history.0.level', 'pastor')
            ->where('history.0.reason', 'Ese mes ya hay convención.'));
});

test('4. varias de la misma clase se aprueban en una sola acción', function () {
    $ids = [($this->proponerYRevisar)('Vigilia de jóvenes')->id, ($this->proponerYRevisar)('Culto de jóvenes')->id];

    $this->actingAs($this->e['pastor'])->post('/approvals/bulk-approve', ['ids' => $ids])
        ->assertSessionHas('notice', 'Listo. Quedaron aprobadas las 2 solicitudes, cada una con su propio registro.');

    expect(Activity::withoutGlobalScopes()->whereIn('title', ['Vigilia de jóvenes', 'Culto de jóvenes'])->pluck('status')->unique()->all())
        ->toBe([Activity::APPROVED]);
});
