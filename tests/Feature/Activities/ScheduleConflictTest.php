<?php

use App\Models\Activity;
use App\Models\ActivityType;
use Illuminate\Support\Carbon;

/*
 * El aviso de cruce (T064; RF-028, CL-08, CE-04; HU-05 escenario 5): si ese dia ya hay otra
 * actividad, de cualquier comite, se advierte cual es y de que comite, sin revelar su presupuesto.
 * Es un aviso, no un bloqueo.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-03-10 10:00', 'America/Bogota'));

    $this->retiro = Activity::factory()->forCommittee($this->e['damas'])->create([
        'title' => 'Retiro de damas',
        'status' => Activity::APPROVED,
        'budget_amount' => 987654,
        'scheduled_at' => Carbon::parse('2026-04-18 19:00', 'America/Bogota'),
        'location' => 'Templo central',
    ]);

    $this->propuesta = [
        'title' => 'Vigilia de jóvenes',
        'activity_type_id' => ActivityType::where('site_id', $this->e['sede']->id)->where('name', 'Culto')->value('id'),
        'date' => '2026-04-18',
        'time' => '19:00',
        'location' => ' templo Central ',
    ];
});

test('si coinciden el día, la hora y el lugar, se advierte qué actividad es y de qué comité, sin revelar su presupuesto', function () {
    $this->actingAs($this->e['liderSonido'])->from('/activities/create')->post('/activities', $this->propuesta)
        ->assertRedirect('/activities/create')
        ->assertSessionHasErrors('conflict');

    expect(session('errors')->first('conflict'))
        ->toContain('a la misma hora y en el mismo lugar, «Retiro de damas» de Comité de Damas Dorcas')
        ->not->toContain('987')
        ->and(Activity::withoutGlobalScopes()->where('title', 'Vigilia de jóvenes')->exists())->toBeFalse();
});

test('es un aviso, no un bloqueo: si insiste, la propuesta queda registrada', function () {
    $this->actingAs($this->e['liderSonido'])->post('/activities', $this->propuesta + ['confirm_conflict' => true])
        ->assertSessionHasNoErrors();

    expect(Activity::withoutGlobalScopes()->where('title', 'Vigilia de jóvenes')->exists())->toBeTrue();
});

test('el mismo día a otra hora también se avisa, sin decir que es la misma hora y lugar', function () {
    $this->actingAs($this->e['liderSonido'])->post('/activities', ['time' => '08:00'] + $this->propuesta)->assertSessionHasErrors('conflict');

    expect(session('errors')->first('conflict'))->toContain('«Retiro de damas»')->not->toContain('a la misma hora');
});

test('otro día no hay aviso', function () {
    $this->actingAs($this->e['liderSonido'])->post('/activities', ['date' => '2026-04-19'] + $this->propuesta)
        ->assertSessionHasNoErrors();
});

test('lo que no se aprobó no cuenta como cruce', function () {
    $this->retiro->update(['status' => Activity::REJECTED, 'rejection_reason' => 'Ese mes no hay presupuesto.']);

    $this->actingAs($this->e['liderSonido'])->post('/activities', $this->propuesta)->assertSessionHasNoErrors();
});
