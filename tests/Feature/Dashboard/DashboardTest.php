<?php

use App\Models\Activity;
use App\Support\Approvals\ApprovalFlow;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * La pantalla de inicio (T084 a T086, HU-17) por los tres cargos: primero lo que espera la decision
 * del cargo y desde cuando; el contador del menu del cargo activo; y lo que el lider envio.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
});

test('1. lo primero que ve quien decide es lo que espera su decisión, lo más antiguo primero', function () {
    $vieja = solicitudDe($this->e['liderSonido'], titulo: 'Vigilia de jóvenes');
    $vieja->update(['requested_at' => now()->subDays(3)]);
    solicitudDe($this->e['liderCoro'], titulo: 'Ensayo del coro');

    $this->actingAs($this->e['presidente'])->get('/dashboard')
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->has('pendingApprovals', 2)
            ->where('pendingApprovals.0.title', 'Vigilia de jóvenes')
            ->where('pendingApprovals.0.waitingSince', fn (string $fecha) => Carbon::parse($fecha)->isSameDay(now()->subDays(3))));

    $this->actingAs($this->e['pastor'])->get('/dashboard')->assertInertia(fn (Assert $page) => $page->has('pendingApprovals', 0));
    $this->actingAs($this->e['liderSonido'])->get('/dashboard')->assertInertia(fn (Assert $page) => $page->has('pendingApprovals', 0));
});

test('2. el contador del menú es del cargo activo', function () {
    solicitudDe($this->e['liderSonido']);
    solicitudDe($this->e['presidentaDamas']);

    $contar = fn ($quien) => $this->actingAs($quien)->withHeaders(pedidoParcial('pendingCount'))->get('/dashboard')->json('props.pendingCount');

    expect($contar($this->e['presidente']))->toBe(1)
        ->and($contar($this->e['pastor']))->toBe(1)
        ->and($contar($this->e['liderSonido']))->toBe(0);
});

test('3. el líder ve en qué va lo que envió, quién la tiene y, si se resolvió, el resultado con su motivo', function () {
    $devuelta = solicitudDe($this->e['liderSonido'], titulo: 'Paseo de sonido');
    app(ApprovalFlow::class)->reject($devuelta, $this->e['presidente'], 'Falta el permiso del templo.');
    $this->travel(1)->minutes();
    solicitudDe($this->e['liderSonido'], titulo: 'Vigilia de jóvenes');

    $mias = collect($this->actingAs($this->e['liderSonido'])->withHeaders(pedidoParcial('myRequests'))->get('/dashboard')->json('props.myRequests'));

    expect($mias->firstWhere('title', 'Vigilia de jóvenes'))->toMatchArray(['status' => 'pending', 'level' => 'president'])
        ->and($mias->firstWhere('title', 'Paseo de sonido'))->toMatchArray([
            'status' => 'rejected',
            'reason' => 'Falta el permiso del templo.',
            'resolvedBy' => 'Hermano Rigoberto Páez',
        ]);
});

test('el inicio dice cómo va el año del cargo y lo que viene', function () {
    $this->travelTo(Carbon::parse('2026-06-15 10:00', 'America/Bogota'));
    Activity::factory()->forCommittee($this->e['sonido'])->create(['status' => Activity::COMPLETED, 'scheduled_at' => Carbon::parse('2026-03-10 19:00', 'America/Bogota')]);
    Activity::factory()->forCommittee($this->e['sonido'])->create(['title' => 'Capacitación de sonido', 'status' => Activity::APPROVED, 'scheduled_at' => Carbon::parse('2026-07-10 19:00', 'America/Bogota')]);

    $this->actingAs($this->e['liderSonido'])->get('/dashboard')
        ->assertInertia(fn (Assert $page) => $page->where('kpis.year', 2026)->where('kpis.compliance', 100)->where('kpis.total', 2));

    $proximas = $this->actingAs($this->e['liderSonido'])->withHeaders(pedidoParcial('upcoming'))->get('/dashboard')->json('props.upcoming');

    expect(collect($proximas)->pluck('title')->all())->toBe(['Capacitación de sonido']);
});
