<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActivityTaskRequest;
use App\Models\Activity;
use App\Models\ActivityTask;
use App\Support\Activities\ActivityStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/**
 * Las tareas de una actividad: el plan de trabajo de su comite. Al marcarlas, el avance y el estado
 * se recalculan solos, sin que nadie los escriba (RF-027, HU-09 escenario 4).
 */
class ActivityTaskController extends Controller
{
    public function store(StoreActivityTaskRequest $request, Activity $activity): RedirectResponse
    {
        if (in_array($activity->status, [Activity::REJECTED, Activity::COMPLETED], true)) {
            throw ValidationException::withMessages(['description' => 'A una actividad cumplida o no aprobada ya no se le agregan tareas.']);
        }

        $activity->tasks()->create([
            'site_id' => $activity->site_id,
            'description' => $request->validated('description'),
            'assigned_to_name' => $request->validated('assigned_to'),
        ]);

        return back()->with('notice', 'Listo. La tarea quedó agregada.');
    }

    public function toggle(Activity $activity, ActivityTask $task): RedirectResponse
    {
        Gate::authorize('update', $activity);
        abort_unless($task->activity_id === $activity->id, 404);

        if (! ActivityStatus::acceptsTaskChecks($activity)) {
            throw ValidationException::withMessages(['tasks' => 'Las tareas se marcan cuando la actividad ya está aprobada y todavía no se ha cumplido.']);
        }

        $task->update(['done' => ! $task->done, 'done_at' => $task->done ? null : now()]);

        return back()->with('notice', $task->done ? 'Listo. La tarea quedó cumplida.' : 'Listo. La tarea quedó pendiente otra vez.');
    }
}
