<?php

use App\Models\Activity;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * La prueba obligatoria de la excepcion 1 al filtro (T079, T218, RG-03, constitucion 3.0.0). Es una
 * lista blanca: lo ajeno trae exactamente los seis datos que se comparten —titulo, fecha, hora de
 * inicio y de fin, lugar, comite y estado— y el numero que lo identifica. Una columna nueva en
 * activities deja esta prueba roja hasta que alguien decida si se comparte. Ver lo ajeno no da
 * permiso de tocarlo, y su ficha responde como fuera del ambito (D-36).
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-03-10 10:00', 'America/Bogota'));
    $this->llaves = ['committee', 'endsAt', 'id', 'location', 'scheduledAt', 'status', 'title'];

    $this->ajena = Activity::factory()->forCommittee($this->e['damas'])->create([
        'title' => 'Retiro de damas',
        'status' => Activity::APPROVED,
        'budget_amount' => 987654,
        'scheduled_at' => Carbon::parse('2026-08-18 19:00', 'America/Bogota'),
        'location' => 'Finca La Esperanza',
        'description' => 'Salimos a las seis desde el templo.',
        'theme' => 'Mujeres de fe',
        'highlight' => 'special_day',
    ]);
    $this->tarea = $this->ajena->tasks()->create(['site_id' => $this->ajena->site_id, 'description' => 'Contratar el bus']);
});

test('en el cronograma, una actividad ajena trae exactamente los seis datos que se comparten', function () {
    $respuesta = $this->actingAs($this->e['presidente'])->get('/activities?year=2026');

    $respuesta->assertInertia(fn (Assert $page) => $page->where('months', function ($meses) {
        $tarjeta = collect($meses)->flatMap(fn ($mes) => $mes['activities'])->firstWhere('id', $this->ajena->id);
        $llaves = array_keys($tarjeta);
        sort($llaves);

        expect($llaves)->toBe($this->llaves);

        return true;
    }));

    $respuesta->assertShows('Retiro de damas')
        ->assertDoesntShow('987654')
        ->assertDoesntShow('Contratar el bus')
        ->assertDoesntShow('Salimos a las seis')
        ->assertDoesntShow('Mujeres de fe');
});

test('la ficha de una actividad ajena responde como fuera del ámbito: el detalle es de su comité', function () {
    $this->actingAs($this->e['presidente'])->get("/activities/{$this->ajena->id}")
        ->assertNotFound()
        ->assertDoesntShow('Retiro de damas');
});

test('el comité dueño sí ve su descripción, su tema y su marca', function () {
    $this->actingAs($this->e['presidentaDamas'])->get("/activities/{$this->ajena->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('activity.description', 'Salimos a las seis desde el templo.')
            ->where('activity.theme', 'Mujeres de fe')
            ->where('activity.highlight', 'special_day'));
});

test('ver una actividad ajena no da permiso de tocarla: todo intento responde no disponible', function () {
    $this->actingAs($this->e['presidente'])->post("/activities/{$this->ajena->id}/tasks/{$this->tarea->id}/toggle")->assertNotFound();
    $this->actingAs($this->e['presidente'])->post("/activities/{$this->ajena->id}/complete")->assertNotFound();
    $this->actingAs($this->e['presidente'])->post("/activities/{$this->ajena->id}/tasks", ['description' => 'Intruso'])->assertNotFound();

    $actividad = Activity::withoutGlobalScopes()->find($this->ajena->id);

    expect($this->tarea->fresh()->done)->toBeFalse()
        ->and($actividad->status)->toBe(Activity::APPROVED)
        ->and($actividad->tasks()->count())->toBe(1);
});

test('lo de otra congregación no aparece ni se abre', function () {
    $otra = Activity::factory()->create(['title' => 'De otra sede', 'status' => Activity::APPROVED, 'scheduled_at' => Carbon::parse('2026-05-05 19:00', 'America/Bogota')]);

    $this->actingAs($this->e['presidente'])->get('/activities?year=2026')->assertDoesntShow('De otra sede');
    $this->actingAs($this->e['presidente'])->get("/activities/{$otra->id}")->assertNotFound();
});

test('lo que no se aprobó en otro comité no aparece en el cronograma', function () {
    Activity::factory()->forCommittee($this->e['damas'])->create([
        'title' => 'Bazar rechazado',
        'status' => Activity::REJECTED,
        'rejection_reason' => 'Ese mes no hay presupuesto.',
        'scheduled_at' => Carbon::parse('2026-05-05 19:00', 'America/Bogota'),
    ]);

    $this->actingAs($this->e['presidente'])->get('/activities?year=2026')->assertDoesntShow('Bazar rechazado');
});
