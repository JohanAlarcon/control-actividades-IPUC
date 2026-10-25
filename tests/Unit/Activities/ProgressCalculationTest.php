<?php

use App\Models\Activity;
use App\Models\ActivityTask;

it('una actividad sin tareas tiene avance cero y no divide por cero', function () {
    $actividad = Activity::factory()->create();

    expect($actividad->progress)->toBe(0);
});

it('calcula la mitad cuando hay dos tareas y una cumplida', function () {
    $actividad = Activity::factory()->create();
    ActivityTask::factory()->forActivity($actividad)->done()->create();
    ActivityTask::factory()->forActivity($actividad)->create();

    expect($actividad->fresh()->progress)->toBe(50);
});

it('llega a cien cuando todas las tareas están cumplidas', function () {
    $actividad = Activity::factory()->create();
    ActivityTask::factory()->forActivity($actividad)->count(3)->done()->create();

    expect($actividad->fresh()->progress)->toBe(100);
});

it('redondea: una de tres tareas da 33', function () {
    $actividad = Activity::factory()->create();
    ActivityTask::factory()->forActivity($actividad)->done()->create();
    ActivityTask::factory()->forActivity($actividad)->count(2)->create();

    expect($actividad->fresh()->progress)->toBe(33);
});

it('recalcula solo cuando se marca una tarea, sin que nadie escriba el avance', function () {
    $actividad = Activity::factory()->create();
    $tarea = ActivityTask::factory()->forActivity($actividad)->create();
    ActivityTask::factory()->forActivity($actividad)->create();

    expect($actividad->fresh()->progress)->toBe(0);

    $tarea->update(['done' => true, 'done_at' => now()]);

    expect($actividad->fresh()->progress)->toBe(50);
});

it('vuelve a bajar si se borra una tarea cumplida', function () {
    $actividad = Activity::factory()->create();
    $cumplida = ActivityTask::factory()->forActivity($actividad)->done()->create();
    ActivityTask::factory()->forActivity($actividad)->create();

    expect($actividad->fresh()->progress)->toBe(50);

    $cumplida->delete();

    expect($actividad->fresh()->progress)->toBe(0);
});
