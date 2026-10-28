<?php

use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\Approval;
use App\Models\CommitteeUser;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Los escenarios de HU-05, con lider y con presidente (T066). El quinto, el cruce impedido, se
 * prueba a fondo en ScheduleConflictTest.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-03-10 10:00', 'America/Bogota'));
    $this->propuesta = [
        'title' => 'Vigilia de jóvenes',
        'activity_type_id' => ActivityType::where('site_id', $this->e['sede']->id)->where('name', 'Culto')->value('id'),
        'date' => '2026-04-18',
        'time' => '19:00',
        'ends' => '21:00',
        'location' => 'Templo central',
        'description' => 'Con alabanza y oración.',
        'budget' => '350000',
        'theme' => 'Y fueron todos llenos del Espíritu Santo',
        'highlight' => 'special_day',
    ];
});

test('1. la propuesta queda registrada y se dice con nombre propio a quién le llegó', function () {
    $this->actingAs($this->e['liderSonido'])->post('/activities', $this->propuesta)
        ->assertRedirect('/approvals')
        ->assertSessionHas('notice', 'Listo. Su propuesta le llegó a Hermano Rigoberto Páez.');

    expect(Activity::sole())
        ->title->toBe('Vigilia de jóvenes')
        ->status->toBe(Activity::PENDING)
        ->committee_id->toBe($this->e['sonido']->id)
        ->proposed_by_user_id->toBe($this->e['liderSonido']->id)
        ->location->toBe('Templo central')
        ->description->toBe('Con alabanza y oración.')
        ->theme->toBe('Y fueron todos llenos del Espíritu Santo')
        ->highlight->toBe('special_day');
});

test('el tema y la marca son opcionales', function () {
    $this->actingAs($this->e['liderSonido'])->post('/activities', ['theme' => '', 'highlight' => ''] + $this->propuesta)->assertSessionHasNoErrors();

    expect(Activity::sole())->theme->toBeNull()->highlight->toBeNull();
});

test('2. lo que propone el líder de un sub-comité le llega primero al presidente de su comité', function () {
    $this->actingAs($this->e['liderCoro'])->post('/activities', $this->propuesta)
        ->assertSessionHas('notice', 'Listo. Su propuesta le llegó a Hermano Rigoberto Páez.');

    expect(Approval::sole())
        ->type->toBe(Approval::TYPE_ACTIVITY)
        ->level->toBe(Approval::LEVEL_PRESIDENT)
        ->committee_id->toBe($this->e['coro']->id);

    $this->actingAs($this->e['presidente'])->get('/approvals')
        ->assertInertia(fn (Assert $page) => $page
            ->where('inbox.0.title', 'Vigilia de jóvenes')
            ->where('inbox.0.requestedBy', 'Hermana Omaira Lozano')
            ->where('inbox.0.activity.location', 'Templo central'));
});

test('3. lo que propone un presidente le llega directamente al pastor', function () {
    $this->actingAs($this->e['presidente'])->post('/activities', $this->propuesta)
        ->assertSessionHas('notice', "Listo. Su propuesta le llegó a {$this->e['pastor']->name}.");

    expect(Approval::sole()->level)->toBe(Approval::LEVEL_PASTOR);
});

test('4. con el nombre vacío o una fecha de otro año no se envía, y se señala qué corregir', function () {
    $this->actingAs($this->e['liderSonido'])->from('/activities/create')
        ->post('/activities', ['title' => '', 'date' => '2027-02-01'] + $this->propuesta)
        ->assertRedirect('/activities/create')
        ->assertSessionHasErrors(['title', 'date']);

    expect(Activity::count())->toBe(0)
        ->and(Approval::count())->toBe(0);
});

test('5. si el horario se cruza con otra actividad, no se guarda y se dice cuál es y de qué comité', function () {
    Activity::factory()->forCommittee($this->e['damas'])->create([
        'title' => 'Retiro de damas',
        'status' => Activity::APPROVED,
        'scheduled_at' => Carbon::parse('2026-04-18 19:00', 'America/Bogota'),
        'location' => 'Templo central',
    ]);

    $this->actingAs($this->e['liderSonido'])->post('/activities', $this->propuesta)->assertSessionHasErrors('conflict');

    expect(session('errors')->first('conflict'))->toContain('«Retiro de damas» de Comité de Damas Dorcas');
});

test('si el comité no tiene presidente, la propuesta queda registrada y se dice que espera al reemplazo', function () {
    CommitteeUser::where('user_id', $this->e['presidente']->id)->sole()->update(['ended_at' => now()]);

    $this->actingAs($this->e['liderCoro'])->post('/activities', $this->propuesta)
        ->assertSessionHas('notice', 'Listo. Su propuesta quedó registrada. Su comité todavía no tiene quién la revise: le llegará a la persona que el pastor nombre.');

    expect(Approval::count())->toBe(1);
});
