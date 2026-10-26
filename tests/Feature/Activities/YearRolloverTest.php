<?php

use App\Models\Activity;
use App\Models\AnnualPlan;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

/* El cambio de año calendario (T076, CL-13): el plan anterior queda consultable y el nuevo empieza sin aprobar. */

test('al cambiar de año, el plan anterior queda consultable y el nuevo empieza sin aprobar', function () {
    $e = estructuraDePrueba();
    $plan = AnnualPlan::create([
        'site_id' => $e['sede']->id,
        'year' => 2026,
        'approved_by_user_id' => $e['pastor']->id,
        'approved_at' => Carbon::parse('2026-01-20 10:00', 'America/Bogota'),
    ]);
    Activity::factory()->forCommittee($e['sonido'])->create([
        'title' => 'Culto de 2026',
        'annual_plan_id' => $plan->id,
        'status' => Activity::APPROVED,
        'scheduled_at' => Carbon::parse('2026-05-10 19:00', 'America/Bogota'),
    ]);

    $this->travelTo(Carbon::parse('2027-01-05 10:00', 'America/Bogota'));

    $this->actingAs($e['presidente'])->get('/activities')
        ->assertInertia(fn (Assert $page) => $page->where('filters.year', 2027)->where('summary.total', 0))
        ->assertDoesntShow('Culto de 2026');

    $this->actingAs($e['presidente'])->get('/activities?year=2026')
        ->assertInertia(fn (Assert $page) => $page->where('filters.year', 2026))
        ->assertShows('Culto de 2026');

    $nuevo = $this->actingAs($e['presidente'])->withHeaders(pedidoParcial('annualPlan', 'Activities/Index'))->get('/activities');
    expect($nuevo->json('props.annualPlan'))->toMatchArray(['year' => 2027, 'approved' => false]);

    $anterior = $this->actingAs($e['presidente'])->withHeaders(pedidoParcial('annualPlan', 'Activities/Index'))->get('/activities?year=2026');
    expect($anterior->json('props.annualPlan'))->toMatchArray(['year' => 2026, 'approved' => true, 'approvedBy' => $e['pastor']->name]);
});
