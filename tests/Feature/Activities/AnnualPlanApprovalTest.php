<?php

use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\AnnualPlan;
use App\Models\Approval;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * El plan del año (T074, T075, T077; HU-08): los comites lo cargan, el pastor lo aprueba en un solo
 * acto, y lo que llega despues se distingue de lo que venia en el plan.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-12-01 10:00', 'America/Bogota'));
    $this->cargar = fn (User $quien, string $titulo, string $fecha, int $anio = 2027) => $this->actingAs($quien)
        ->from('/activities/create?plan='.$anio)
        ->post("/annual-plan/{$anio}/activities", [
            'title' => $titulo,
            'activity_type_id' => ActivityType::withoutGlobalScopes()->where('site_id', $this->e['sede']->id)->where('name', 'Culto')->value('id'),
            'date' => $fecha,
            'time' => '19:00',
            'confirm_conflict' => true,
        ]);
});

test('1. el pastor aprueba el plan en un solo acto: todas sus actividades quedan aprobadas con su nombre y la fecha', function () {
    ($this->cargar)($this->e['liderSonido'], 'Vigilia de enero', '2027-01-15')
        ->assertRedirect('/activities?year=2027')
        ->assertSessionHas('notice', 'Listo. «Vigilia de enero» quedó en el plan 2027. Entra al cronograma cuando el pastor apruebe el plan completo.');
    ($this->cargar)($this->e['presidentaDamas'], 'Retiro de damas', '2027-03-20');
    ($this->cargar)($this->e['presidente'], 'Culto de aniversario', '2027-02-10');

    $this->actingAs($this->e['pastor'])->post('/annual-plan/2027/approve')
        ->assertSessionHas('notice', 'Listo. El plan 2027 quedó aprobado: 3 actividades entraron al cronograma.');

    $plan = AnnualPlan::withoutGlobalScopes()->where('year', 2027)->sole();
    $actividades = Activity::withoutGlobalScopes()->where('annual_plan_id', $plan->id)->get();

    expect($plan->approved_by_user_id)->toBe($this->e['pastor']->id)
        ->and($plan->approved_at)->not->toBeNull()
        ->and($actividades)->toHaveCount(3)
        ->and($actividades->pluck('status')->unique()->all())->toBe([Activity::APPROVED])
        ->and($actividades->pluck('approved_by_user_id')->unique()->all())->toBe([$this->e['pastor']->id])
        ->and(Approval::withoutGlobalScopes()->count())->toBe(0);
});

test('2. el cronograma dice quién aprobó el plan y cuándo', function () {
    ($this->cargar)($this->e['liderSonido'], 'Vigilia de enero', '2027-01-15');
    $this->actingAs($this->e['pastor'])->post('/annual-plan/2027/approve');

    $respuesta = $this->actingAs($this->e['liderCoro'])->withHeaders(pedidoParcial('annualPlan', 'Activities/Index'))->get('/activities?year=2027');

    expect($respuesta->json('props.annualPlan'))
        ->toMatchArray(['year' => 2027, 'approved' => true, 'approvedBy' => $this->e['pastor']->name])
        ->and($respuesta->json('props.annualPlan.approvedAt'))->not->toBeNull();
});

test('3. lo propuesto después del plan se distingue de lo que venía en él', function () {
    $this->travelTo(Carbon::parse('2026-03-10 10:00', 'America/Bogota'));
    $plan = AnnualPlan::create(['site_id' => $this->e['sede']->id, 'year' => 2026, 'approved_by_user_id' => $this->e['pastor']->id, 'approved_at' => now()->subMonth()]);
    Activity::factory()->forCommittee($this->e['sonido'])->create([
        'title' => 'Del plan', 'annual_plan_id' => $plan->id, 'status' => Activity::APPROVED, 'scheduled_at' => Carbon::parse('2026-05-10 19:00', 'America/Bogota'),
    ]);
    Activity::factory()->forCommittee($this->e['sonido'])->create([
        'title' => 'Propuesta después', 'status' => Activity::APPROVED, 'scheduled_at' => Carbon::parse('2026-05-20 19:00', 'America/Bogota'),
    ]);

    $this->actingAs($this->e['presidente'])->get('/activities?year=2026')
        ->assertInertia(fn (Assert $page) => $page
            ->where('months.4.activities.0.title', 'Del plan')
            ->where('months.4.activities.0.fromPlan', true)
            ->where('months.4.activities.1.title', 'Propuesta después')
            ->where('months.4.activities.1.fromPlan', false));
});

test('con el plan aprobado ya no se le carga nada: lo nuevo va como propuesta aparte', function () {
    ($this->cargar)($this->e['liderSonido'], 'Vigilia de enero', '2027-01-15');
    $this->actingAs($this->e['pastor'])->post('/annual-plan/2027/approve');

    ($this->cargar)($this->e['liderSonido'], 'Otra vigilia', '2027-02-15')
        ->assertSessionHasErrors(['plan' => 'El plan 2027 ya está aprobado. Lo nuevo va como propuesta aparte.']);
});

test('solo el pastor aprueba el plan', function () {
    ($this->cargar)($this->e['liderSonido'], 'Vigilia de enero', '2027-01-15');

    $this->actingAs($this->e['presidente'])->post('/annual-plan/2027/approve')->assertForbidden();

    expect(AnnualPlan::withoutGlobalScopes()->where('year', 2027)->sole()->approved_at)->toBeNull();
});

test('se carga al plan de este año o del siguiente, y con fechas de ese año', function () {
    ($this->cargar)($this->e['liderSonido'], 'Muy atrás', '2025-05-15', 2025)->assertNotFound();
    ($this->cargar)($this->e['liderSonido'], 'Muy adelante', '2028-05-15', 2028)->assertNotFound();
    ($this->cargar)($this->e['liderSonido'], 'Fecha de otro año', '2026-12-20')
        ->assertSessionHasErrors(['date' => 'La fecha debe ser del 2027, el año del plan.']);
});
