<?php

use App\Models\Activity;
use App\Support\Activities\ActivityStatus;
use App\Support\Activities\ScheduleSummary;
use Illuminate\Support\Carbon;

/*
 * Los estados de una actividad (T167, R-04): las seis transiciones y las que no se permiten. Y el
 * cumplimiento del cronograma, que mide solo lo que ya debia hacerse (RF-030).
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-06-15 10:00', 'America/Bogota'));

    $this->actividad = function (string $estado, string $fecha = '2026-08-20 19:00', int $tareas = 3): Activity {
        $actividad = Activity::factory()->forCommittee($this->e['sonido'])->create([
            'status' => $estado,
            'scheduled_at' => Carbon::parse($fecha, 'America/Bogota'),
        ]);

        for ($n = 1; $n <= $tareas; $n++) {
            $actividad->tasks()->create(['site_id' => $actividad->site_id, 'description' => "Tarea {$n}"]);
        }

        return $actividad;
    };

    $this->marcar = fn (Activity $actividad, int $cuantas) => $actividad->tasks()->orderBy('id')->take($cuantas)->get()
        ->each(fn ($tarea) => $tarea->update(['done' => true, 'done_at' => now()]));
});

test('una aprobada pasa a en curso al cumplirse la primera tarea', function () {
    $actividad = ($this->actividad)(Activity::APPROVED);
    ($this->marcar)($actividad, 1);

    expect($actividad->fresh())->status->toBe(Activity::IN_PROGRESS)->progress->toBe(33);
});

test('una aprobada cuya fecha llegó aparece en curso, aunque no tenga tareas cumplidas', function () {
    expect(ActivityStatus::effective(Activity::APPROVED, now()->subHour()))->toBe(Activity::IN_PROGRESS)
        ->and(ActivityStatus::effective(Activity::APPROVED, now()->addDay()))->toBe(Activity::APPROVED)
        ->and(ActivityStatus::effective(Activity::PENDING, now()->subDay()))->toBe(Activity::PENDING);
});

test('en curso pasa a cumplida al terminar todas sus tareas, y queda la fecha', function () {
    $actividad = ($this->actividad)(Activity::APPROVED);
    ($this->marcar)($actividad, 3);

    expect($actividad->fresh())->status->toBe(Activity::COMPLETED)->progress->toBe(100)->completed_at->not->toBeNull();
});

test('una cumplida no vuelve a en curso aunque se desmarque una tarea', function () {
    $actividad = ($this->actividad)(Activity::APPROVED);
    ($this->marcar)($actividad, 3);

    $actividad->tasks()->first()->update(['done' => false, 'done_at' => null]);

    expect($actividad->fresh())->status->toBe(Activity::COMPLETED)->progress->toBe(67);
});

test('lo que espera aprobación o no se aprobó no cambia con las tareas', function (string $estado) {
    $actividad = ($this->actividad)($estado);
    ($this->marcar)($actividad, 3);

    expect($actividad->fresh()->status)->toBe($estado);
})->with([Activity::PENDING, Activity::REJECTED]);

test('sin tareas, el responsable la marca cumplida; con tareas, o sin aprobar, no', function () {
    expect(ActivityStatus::canMarkCompleted(($this->actividad)(Activity::APPROVED, tareas: 0), 0))->toBeTrue()
        ->and(ActivityStatus::canMarkCompleted(($this->actividad)(Activity::IN_PROGRESS, tareas: 0), 0))->toBeTrue()
        ->and(ActivityStatus::canMarkCompleted(($this->actividad)(Activity::APPROVED), 3))->toBeFalse()
        ->and(ActivityStatus::canMarkCompleted(($this->actividad)(Activity::PENDING, tareas: 0), 0))->toBeFalse()
        ->and(ActivityStatus::canMarkCompleted(($this->actividad)(Activity::COMPLETED, tareas: 0), 0))->toBeFalse();
});

test('el cumplimiento mide solo lo que ya debía hacerse', function () {
    $tarjeta = fn (string $estado, string $fecha) => ['status' => $estado, 'scheduledAt' => Carbon::parse($fecha, 'America/Bogota')->toIso8601String()];

    $resumen = ScheduleSummary::of(collect([
        $tarjeta(Activity::COMPLETED, '2026-03-01'),
        $tarjeta(Activity::COMPLETED, '2026-04-01'),
        $tarjeta(Activity::IN_PROGRESS, '2026-05-01'),
        $tarjeta(Activity::APPROVED, '2026-09-01'),
        $tarjeta(Activity::PENDING, '2026-02-01'),
        $tarjeta(Activity::COMPLETED, '2026-10-01'),
    ]));

    expect($resumen)->toMatchArray(['total' => 6, 'completed' => 3, 'inProgress' => 1, 'approved' => 1, 'pending' => 1, 'due' => 4, 'compliance' => 75]);
});

test('sin nada que ya debiera hacerse, no hay porcentaje que mostrar', function () {
    $resumen = ScheduleSummary::of(collect([['status' => Activity::APPROVED, 'scheduledAt' => now()->addMonth()->toIso8601String()]]));

    expect($resumen['compliance'])->toBeNull()->and($resumen['due'])->toBe(0);
});
