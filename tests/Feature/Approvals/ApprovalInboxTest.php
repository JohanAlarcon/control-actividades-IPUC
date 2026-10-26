<?php

use App\Support\Approvals\ApprovalFlow;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * La bandeja (T068). Cada tarjeta se lee completa sin abrir nada, y nadie ve las solicitudes de
 * un comite que no es el suyo (principio I).
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
});

test('la tarjeta dice de dónde viene, quién la pidió, qué mueve en el cronograma y desde cuándo espera', function () {
    $solicitud = $this->travelTo(now()->subDays(4), fn () => solicitudDe($this->e['liderMontaje'], titulo: 'Montaje de la convención'));
    $solicitud->approvable->update(['scheduled_at' => '2026-11-07 19:00:00', 'location' => 'Templo central']);

    $this->actingAs($this->e['presidente'])->get('/approvals')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Approvals/Index')
            ->where('counts.inbox', 1)
            ->where('inbox.0.title', 'Montaje de la convención')
            ->where('inbox.0.committee', 'Equipo de Montaje')
            ->where('inbox.0.requestedBy', 'Hermano Wilson Garzón')
            ->where('inbox.0.activity.location', 'Templo central')
            ->where('inbox.0.activity.date', fn (string $fecha) => Carbon::parse($fecha)->equalTo('2026-11-07 19:00:00'))
            ->where('inbox.0.waitingSince', fn (string $fecha) => Carbon::parse($fecha)->isSameDay(now()->subDays(4))));
});

test('el gasto trae su monto', function () {
    solicitudDe($this->e['liderSonido'], 'expense', 'Cables nuevos', 850_000);

    $this->actingAs($this->e['presidente'])->get('/approvals')
        ->assertInertia(fn (Assert $page) => $page->where('inbox.0.type', 'expense')->where('inbox.0.amount', fn ($monto) => (float) $monto === 850000.0));
});

test('al pastor le llega lo que el presidente ya aprobó, diciendo por quién pasó', function () {
    $solicitud = app(ApprovalFlow::class)->approve(solicitudDe($this->e['liderSonido']), $this->e['presidente']);

    $this->actingAs($this->e['pastor'])->get('/approvals')
        ->assertInertia(fn (Assert $page) => $page
            ->where('counts.inbox', 1)
            ->where('inbox.0.id', $solicitud->id)
            ->where('inbox.0.escalatedBy', 'Hermano Rigoberto Páez'));
});

test('lo que el presidente ya pasó al pastor le queda a la vista, esperando', function () {
    $solicitud = app(ApprovalFlow::class)->approve(solicitudDe($this->e['liderSonido']), $this->e['presidente']);

    $this->actingAs($this->e['presidente'])->get('/approvals')
        ->assertInertia(fn (Assert $page) => $page
            ->where('counts.inbox', 0)
            ->where('counts.escalated', 1)
            ->where('escalated.0.id', $solicitud->id)
            ->where('escalated.0.level', 'pastor'));
});

test('nadie ve las solicitudes de un comité que no es el suyo', function () {
    solicitudDe($this->e['liderSonido'], titulo: 'Prueba de micrófonos');

    $this->actingAs($this->e['presidentaDamas'])->get('/approvals')
        ->assertOk()
        ->assertDoesntShow('Prueba de micrófonos');

    $this->actingAs($this->e['liderCoro'])->get('/approvals')
        ->assertOk()
        ->assertDoesntShow('Prueba de micrófonos');
});
