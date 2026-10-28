<?php

use App\Models\Activity;
use App\Support\Approvals\ApprovalFlow;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

/* La ficha de una actividad propia (T082, HU-09 escenario 4): tareas, avance, rastro y cumplida. */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-06-15 10:00', 'America/Bogota'));
    $this->actividad = function (string $estado = Activity::APPROVED, int $tareas = 2): Activity {
        $actividad = Activity::factory()->forCommittee($this->e['sonido'])->create([
            'title' => 'Capacitación de sonido',
            'status' => $estado,
            'scheduled_at' => Carbon::parse('2026-08-20 19:00', 'America/Bogota'),
            'proposed_by_user_id' => $this->e['liderSonido']->id,
        ]);

        for ($n = 1; $n <= $tareas; $n++) {
            $actividad->tasks()->create(['site_id' => $actividad->site_id, 'description' => "Tarea {$n}"]);
        }

        return $actividad;
    };
});

test('marcar una tarea recalcula el avance y el estado, sin que nadie los escriba', function () {
    $actividad = ($this->actividad)();
    $tarea = $actividad->tasks()->orderBy('id')->first();

    $this->actingAs($this->e['liderSonido'])->post("/activities/{$actividad->id}/tasks/{$tarea->id}/toggle")
        ->assertSessionHas('notice', 'Listo. La tarea quedó cumplida.');

    expect($actividad->fresh())->progress->toBe(50)->status->toBe(Activity::IN_PROGRESS);

    $this->actingAs($this->e['liderSonido'])->get("/activities/{$actividad->id}")
        ->assertInertia(fn (Assert $page) => $page
            ->component('Activities/Show')
            ->where('activity.progress', 50)
            ->where('tasks.0.done', true)
            ->where('canCheckTasks', true));
});

test('el rastro dice quién la propuso, quién le dio el visto bueno y quién la aprobó', function () {
    $flujo = app(ApprovalFlow::class);
    $solicitud = $flujo->approve(solicitudDe($this->e['liderSonido'], titulo: 'Vigilia de jóvenes'), $this->e['presidente']);
    $flujo->approve($solicitud, $this->e['pastor']);

    $respuesta = $this->actingAs($this->e['liderSonido'])
        ->withHeaders(pedidoParcial('trail', 'Activities/Show'))
        ->get("/activities/{$solicitud->approvable_id}");

    expect(collect($respuesta->json('props.trail'))->pluck('text')->all())->toBe([
        'Propuesta por Hermano Andrés Molina',
        'Visto bueno de Hermano Rigoberto Páez',
        "Aprobada por {$this->e['pastor']->name}",
    ]);
});

test('sin tareas, el responsable la marca cumplida, y queda su nombre', function () {
    $actividad = ($this->actividad)(tareas: 0);

    $this->actingAs($this->e['liderSonido'])->post("/activities/{$actividad->id}/complete")
        ->assertSessionHas('notice', 'Listo. La actividad quedó cumplida, con su nombre y la fecha.');

    expect($actividad->fresh())
        ->status->toBe(Activity::COMPLETED)
        ->completed_by_user_id->toBe($this->e['liderSonido']->id);
});

test('con tareas no se marca cumplida a mano: se cumple sola', function () {
    $actividad = ($this->actividad)();

    $this->actingAs($this->e['liderSonido'])->from("/activities/{$actividad->id}")->post("/activities/{$actividad->id}/complete")
        ->assertSessionHasErrors(['activity' => 'Esta actividad se cumple sola cuando se terminan todas sus tareas.']);
});

test('se le agregan tareas, con quién las hace; sin descripción no', function () {
    $actividad = ($this->actividad)(tareas: 0);

    $this->actingAs($this->e['liderSonido'])->post("/activities/{$actividad->id}/tasks", ['description' => 'Probar los micrófonos', 'assigned_to' => 'Hermano Julián'])
        ->assertSessionHas('notice', 'Listo. La tarea quedó agregada.');
    $this->actingAs($this->e['liderSonido'])->post("/activities/{$actividad->id}/tasks", ['description' => ''])
        ->assertSessionHasErrors(['description' => 'Escriba qué hay que hacer. Por ejemplo: Reservar el templo.']);

    expect($actividad->tasks()->sole())->description->toBe('Probar los micrófonos')->assigned_to_name->toBe('Hermano Julián');
});

test('a lo que espera aprobación no se le marcan tareas', function () {
    $actividad = ($this->actividad)(Activity::PENDING);
    $tarea = $actividad->tasks()->first();

    $this->actingAs($this->e['liderSonido'])->from("/activities/{$actividad->id}")->post("/activities/{$actividad->id}/tasks/{$tarea->id}/toggle")
        ->assertSessionHasErrors('tasks');

    expect($tarea->fresh()->done)->toBeFalse();
});

it('la ficha cabe en el presupuesto y no crece con las tareas', function () {
    $actividad = ($this->actividad)();
    $medir = function () use ($actividad) {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($this->e['liderSonido'])->get("/activities/{$actividad->id}")->assertOk();
        $consultas = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $consultas;
    };

    $antes = $medir();

    foreach (range(1, 8) as $n) {
        $actividad->tasks()->create(['site_id' => $actividad->site_id, 'description' => "Otra tarea {$n}"]);
    }

    expect($antes)->toBeLessThanOrEqual(5)
        ->and($medir())->toBe($antes);
});
