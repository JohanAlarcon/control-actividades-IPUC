<?php

use App\Models\Activity;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * La prueba obligatoria de la unica excepcion al filtro (T079, RG-03, constitucion v2.0.0). Es una
 * lista blanca: lo ajeno trae exactamente los siete datos que se comparten, y el numero para
 * abrirlo. Una columna nueva en activities deja esta prueba roja hasta que alguien decida si se
 * comparte. Y ver lo ajeno no da permiso de tocarlo.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-03-10 10:00', 'America/Bogota'));
    $this->llaves = ['committee', 'description', 'id', 'location', 'scheduledAt', 'status', 'title'];

    $this->ajena = Activity::factory()->forCommittee($this->e['damas'])->create([
        'title' => 'Retiro de damas',
        'status' => Activity::APPROVED,
        'budget_amount' => 987654,
        'scheduled_at' => Carbon::parse('2026-08-18 19:00', 'America/Bogota'),
        'location' => 'Finca La Esperanza',
    ]);
    $this->tarea = $this->ajena->tasks()->create(['site_id' => $this->ajena->site_id, 'description' => 'Contratar el bus']);
});

test('en el cronograma, una actividad ajena trae exactamente los siete datos que se comparten', function () {
    $respuesta = $this->actingAs($this->e['presidente'])->get('/activities?year=2026');

    $respuesta->assertInertia(fn (Assert $page) => $page->where('months', function ($meses) {
        $tarjeta = collect($meses)->flatMap(fn ($mes) => $mes['activities'])->firstWhere('id', $this->ajena->id);
        $llaves = array_keys($tarjeta);
        sort($llaves);

        expect($llaves)->toBe($this->llaves);

        return true;
    }));

    $respuesta->assertShows('Retiro de damas')->assertDoesntShow('987654')->assertDoesntShow('Contratar el bus');
});

test('en la ficha, también: los siete datos y nada más, sin tareas ni rastro', function () {
    $this->actingAs($this->e['presidente'])->get("/activities/{$this->ajena->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Activities/Show')
            ->where('mine', false)
            ->where('activity', function ($actividad) {
                expect($actividad->keys()->sort()->values()->all())->toBe($this->llaves);

                return true;
            })
            ->missing('tasks')
            ->missing('trail'))
        ->assertDoesntShow('987654');
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
