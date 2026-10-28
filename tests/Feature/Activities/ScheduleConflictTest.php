<?php

use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\Approval;
use Illuminate\Support\Carbon;

/*
 * El cruce en el cronograma general (T219; RF-028, D-37, CL-08, P-02; HU-05 escenario 5): dos
 * actividades el mismo dia con horarios que se sobreponen, de cualquier comite y en cualquier
 * lugar, no se guardan. El mensaje dice cual es y de que comite, sin revelar su presupuesto. Las
 * contiguas si se pueden, y al aprobar se revisa otra vez.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-03-10 10:00', 'America/Bogota'));

    $this->retiro = Activity::factory()->forCommittee($this->e['damas'])->create([
        'title' => 'Retiro de damas',
        'status' => Activity::APPROVED,
        'budget_amount' => 987654,
        'scheduled_at' => Carbon::parse('2026-04-18 19:00', 'America/Bogota'),
        'ends_at' => Carbon::parse('2026-04-18 21:00', 'America/Bogota'),
        'location' => 'Templo central',
    ]);

    $this->propuesta = [
        'title' => 'Vigilia de jóvenes',
        'activity_type_id' => ActivityType::where('site_id', $this->e['sede']->id)->where('name', 'Culto')->value('id'),
        'date' => '2026-04-18',
        'time' => '20:00',
        'ends' => '23:00',
        'location' => 'Salón social',
    ];
});

test('un horario que se sobrepone no se guarda, y se dice cuál actividad es y de qué comité, sin su presupuesto', function () {
    $this->actingAs($this->e['liderSonido'])->from('/activities/create')->post('/activities', $this->propuesta)
        ->assertRedirect('/activities/create')
        ->assertSessionHasErrors('conflict');

    expect(session('errors')->first('conflict'))
        ->toContain('«Retiro de damas» de Comité de Damas Dorcas, de 7:00')
        ->toContain('no se pueden cruzar')
        ->not->toContain('987')
        ->and(Activity::withoutGlobalScopes()->where('title', 'Vigilia de jóvenes')->exists())->toBeFalse();
});

test('se cruza aunque sea en otro lugar, y aunque la otra todavía espere aprobación (P-02)', function () {
    $this->retiro->update(['status' => Activity::PENDING]);

    $this->actingAs($this->e['liderSonido'])->post('/activities', ['time' => '17:00', 'ends' => '19:30'] + $this->propuesta)
        ->assertSessionHasErrors('conflict');
});

test('no hay forma de insistir: el cruce se impide', function () {
    $this->actingAs($this->e['liderSonido'])->post('/activities', $this->propuesta + ['confirm_conflict' => true])
        ->assertSessionHasErrors('conflict');

    expect(Activity::withoutGlobalScopes()->where('title', 'Vigilia de jóvenes')->exists())->toBeFalse();
});

test('las contiguas sí: una empieza a la hora en que termina la otra', function () {
    $this->actingAs($this->e['liderSonido'])->post('/activities', ['time' => '21:00', 'ends' => '22:00'] + $this->propuesta)->assertSessionHasNoErrors();
    $this->actingAs($this->e['liderCoro'])->post('/activities', ['title' => 'Ensayo del coro', 'time' => '17:00', 'ends' => '19:00'] + $this->propuesta)->assertSessionHasNoErrors();

    expect(Activity::withoutGlobalScopes()->count())->toBe(3);
});

test('otro día, o lo que no se aprobó, no se cruza', function () {
    $this->actingAs($this->e['liderSonido'])->post('/activities', ['date' => '2026-04-19'] + $this->propuesta)->assertSessionHasNoErrors();

    $this->retiro->update(['status' => Activity::REJECTED, 'rejection_reason' => 'Ese mes no hay presupuesto.']);
    $this->actingAs($this->e['liderCoro'])->post('/activities', ['title' => 'Ensayo del coro'] + $this->propuesta)->assertSessionHasNoErrors();
});

test('también al cargar al plan del año siguiente', function () {
    $this->travelTo(Carbon::parse('2026-12-01 10:00', 'America/Bogota'));
    Activity::factory()->forCommittee($this->e['damas'])->create([
        'title' => 'Convención de damas',
        'scheduled_at' => Carbon::parse('2027-02-13 18:00', 'America/Bogota'),
        'ends_at' => Carbon::parse('2027-02-13 22:00', 'America/Bogota'),
    ]);

    $this->actingAs($this->e['liderSonido'])->post('/annual-plan/2027/activities', ['date' => '2027-02-13'] + $this->propuesta)
        ->assertSessionHasErrors('conflict');
});

test('CL-08: al aprobar se revisa otra vez, y de dos propuestas que se cruzan solo pasa la primera', function () {
    $this->retiro->update(['scheduled_at' => Carbon::parse('2026-04-25 19:00', 'America/Bogota'), 'ends_at' => Carbon::parse('2026-04-25 21:00', 'America/Bogota')]);
    $primera = solicitudDe($this->e['presidente'], titulo: 'Culto unido');
    $segunda = solicitudDe($this->e['presidentaDamas'], titulo: 'Culto de damas');
    // Llegaron al mismo tiempo, antes de que ninguna apartara el horario.
    $segunda->approvable->update(['scheduled_at' => $primera->approvable->scheduled_at, 'ends_at' => $primera->approvable->ends_at]);

    $this->actingAs($this->e['pastor'])->post("/approvals/{$primera->id}/approve")->assertSessionHasNoErrors();
    $this->actingAs($this->e['pastor'])->post("/approvals/{$segunda->id}/approve")->assertSessionHasErrors('approval');

    expect(session('errors')->first('approval'))->toContain('«Culto de damas» no se puede aprobar. Ese horario se cruza con «Culto unido» de Comité de Alabanza')
        ->and($segunda->fresh()->status)->toBe(Approval::PENDING)
        ->and($segunda->approvable->fresh()->status)->toBe(Activity::PENDING);
});
