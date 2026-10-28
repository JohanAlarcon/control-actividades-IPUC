<?php

use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\Schedule;
use App\Models\Site;
use App\Models\User;
use App\Support\Activities\ScheduleConflict;
use App\Support\Scope\CurrentScope;
use Illuminate\Support\Carbon;

/*
 * Los cronogramas (T224; D-35, RF-086): el general de cada año, uno por sede, y los internos de
 * cada comité o sub-comité. Lo interno no se comparte: no entra al calendario de los demás, ni al
 * cronograma general del mismo comité, ni cuenta para los cruces (RF-087).
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
    ];
});

test('lo que se propone y lo que se carga al plan queda en el cronograma general de su año', function () {
    $this->actingAs($this->e['presidente'])->post('/activities', $this->propuesta)->assertSessionHasNoErrors();
    $this->actingAs($this->e['presidente'])->post('/annual-plan/2027/activities', ['date' => '2027-02-06'] + $this->propuesta)->assertSessionHasNoErrors();

    $cronogramas = Activity::query()->where('title', 'Vigilia de jóvenes')->orderBy('scheduled_at')->get()
        ->map(fn (Activity $actividad) => $actividad->schedule->only(['kind', 'year', 'committee_id']))
        ->all();

    expect($cronogramas)->toBe([
        ['kind' => 'general', 'year' => 2026, 'committee_id' => null],
        ['kind' => 'general', 'year' => 2027, 'committee_id' => null],
    ]);
});

test('el cronograma general es uno por sede y año, y va de enero a diciembre', function () {
    $general = Schedule::generalOf($this->e['sede']->id, 2026);

    expect(Schedule::generalOf($this->e['sede']->id, 2026)->id)->toBe($general->id)
        ->and(Schedule::generalOf($this->e['sede']->id, 2027)->id)->not->toBe($general->id)
        ->and(Schedule::generalOf(Site::factory()->create()->id, 2026)->id)->not->toBe($general->id)
        ->and($general->starts_on->toDateString())->toBe('2026-01-01')
        ->and($general->ends_on->toDateString())->toBe('2026-12-31');
});

test('un interno lo ven su comité, sus ascendientes y el pastor; el general, todos (RF-086)', function () {
    $general = Schedule::generalOf($this->e['sede']->id, 2026);
    $deAlabanza = Schedule::factory()->internalOf($this->e['alabanza'], 'Ensayos del semestre')->create();
    $deSonido = Schedule::factory()->internalOf($this->e['sonido'], 'Mantenimiento de equipos')->create();
    $deDamas = Schedule::factory()->internalOf($this->e['damas'], 'Módulo 1')->create();

    $visibles = function (User $quien) {
        app(CurrentScope::class)->resolve($quien);

        return Schedule::query()->orderBy('id')->pluck('id')->all();
    };

    expect($visibles($this->e['presidente']))->toBe([$general->id, $deAlabanza->id, $deSonido->id])
        ->and($visibles($this->e['liderSonido']))->toBe([$general->id, $deSonido->id])
        ->and($visibles($this->e['presidentaDamas']))->toBe([$general->id, $deDamas->id])
        ->and($visibles($this->e['pastor']))->toBe([$general->id, $deAlabanza->id, $deSonido->id, $deDamas->id]);

    app(CurrentScope::class)->resolve($this->e['presidente']);
    expect(Schedule::find($deDamas->id))->toBeNull();
});

test('lo interno no entra al calendario compartido, ni al cronograma general, ni cuenta para los cruces', function () {
    $modulo = Schedule::factory()->internalOf($this->e['damas'], 'Clases de Semillitas de Amor')->create();
    Activity::factory()->forCommittee($this->e['damas'])->approved()->create([
        'schedule_id' => $modulo->id,
        'title' => 'Clase de Semillitas',
        'scheduled_at' => Carbon::parse('2026-04-18 19:00', 'America/Bogota'),
        'ends_at' => Carbon::parse('2026-04-18 21:00', 'America/Bogota'),
    ]);
    Activity::factory()->forCommittee($this->e['damas'])->approved()->create([
        'title' => 'Vigilia de damas',
        'scheduled_at' => Carbon::parse('2026-05-02 19:00', 'America/Bogota'),
        'ends_at' => Carbon::parse('2026-05-02 21:00', 'America/Bogota'),
    ]);

    $this->actingAs($this->e['presidente'])->get('/activities?year=2026')
        ->assertShows('Vigilia de damas')
        ->assertDoesntShow('Clase de Semillitas')
        ->assertDoesntShow('Semillitas de Amor');
    $this->actingAs($this->e['presidentaDamas'])->get('/activities?year=2026')
        ->assertShows('Vigilia de damas')
        ->assertDoesntShow('Clase de Semillitas');
    $this->actingAs($this->e['presidentaDamas'])->get('/activities/print?year=2026')
        ->assertDoesntShow('Clase de Semillitas');

    expect(ScheduleConflict::with(Carbon::parse('2026-04-18 19:30', 'America/Bogota'), Carbon::parse('2026-04-18 20:30', 'America/Bogota')))->toBe([]);
    $this->actingAs($this->e['presidente'])->post('/activities', $this->propuesta)->assertSessionHasNoErrors();
});
