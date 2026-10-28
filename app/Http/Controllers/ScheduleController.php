<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInternalActivityRequest;
use App\Http\Requests\StoreScheduleRequest;
use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\Schedule;
use App\Support\Scope\CurrentScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Los cronogramas internos de cada comité (HU-27; RF-086, RF-087): los ensayos, las clases y las
 * reuniones que no van en el cronograma general. Los ve el comité dueño, sus ascendientes y el
 * pastor; los demás no saben que existen. Sus actividades no pasan por aprobación y se cruzan.
 */
class ScheduleController extends Controller
{
    public function __construct(private readonly CurrentScope $scope) {}

    public function index(): Response
    {
        $cronogramas = Schedule::query()
            ->join('committees', 'committees.id', '=', 'schedules.committee_id')
            ->where('schedules.kind', Schedule::INTERNAL)
            ->select(['schedules.*', 'committees.name as committee_name'])
            ->withCount('activities')
            ->orderByDesc('schedules.starts_on')
            ->orderBy('schedules.name')
            ->get();

        return Inertia::render('Schedules/Index', [
            'schedules' => $cronogramas->map(fn (Schedule $cronograma) => [
                'id' => $cronograma->id,
                'name' => $cronograma->name,
                'committee' => $cronograma->committee_name,
                'startsOn' => $cronograma->starts_on->toDateString(),
                'endsOn' => $cronograma->ends_on->toDateString(),
                'activities' => $cronograma->activities_count,
            ])->all(),
            // El comité para el que se crearía uno nuevo; nulo si el cargo no crea cronogramas.
            'createFor' => Gate::allows('create', Schedule::class) ? $this->scope->activeRole()->committee?->name : null,
        ]);
    }

    public function store(StoreScheduleRequest $request): RedirectResponse
    {
        $cargo = $this->scope->activeRole();

        $cronograma = Schedule::create([
            'site_id' => $cargo->site_id,
            'committee_id' => $cargo->committee_id,
            'kind' => Schedule::INTERNAL,
            'year' => (int) substr($request->validated('starts_on'), 0, 4),
            'name' => $request->validated('name'),
            'starts_on' => $request->validated('starts_on'),
            'ends_on' => $request->validated('ends_on'),
        ]);

        return redirect("/schedules/{$cronograma->id}")->with('notice', "Listo. El cronograma «{$cronograma->name}» quedó creado. Ya puede agregarle actividades.");
    }

    public function show(int $schedule): Response
    {
        // El cronograma y el nombre de su comité en una sola consulta, para no pasarse de cinco. Pasa
        // por el filtro del cargo: si es ajeno, del general o no existe, no vuelve nada.
        $cronograma = Schedule::query()
            ->join('committees', 'committees.id', '=', 'schedules.committee_id')
            ->where('schedules.kind', Schedule::INTERNAL)
            ->select(['schedules.*', 'committees.name as committee_name'])
            ->findOrFail($schedule);
        Gate::authorize('view', $cronograma);

        $puedeAgregar = Gate::allows('addActivity', $cronograma);

        return Inertia::render('Schedules/Show', [
            'schedule' => [
                'id' => $cronograma->id,
                'name' => $cronograma->name,
                'committee' => $cronograma->committee_name,
                'startsOn' => $cronograma->starts_on->toDateString(),
                'endsOn' => $cronograma->ends_on->toDateString(),
            ],
            'activities' => $cronograma->activities()
                ->leftJoin('activity_types', 'activity_types.id', '=', 'activities.activity_type_id')
                ->orderBy('activities.scheduled_at')
                ->get(['activities.id', 'activities.title', 'activities.theme', 'activities.scheduled_at', 'activities.ends_at', 'activities.location', 'activities.responsible_name', 'activity_types.name as type_name'])
                ->map(fn (Activity $actividad) => [
                    'id' => $actividad->id,
                    'title' => $actividad->title,
                    'theme' => $actividad->theme,
                    'type' => $actividad->type_name,
                    'scheduledAt' => $actividad->scheduled_at->toIso8601String(),
                    'endsAt' => $actividad->ends_at->toIso8601String(),
                    'location' => $actividad->location,
                    'responsible' => $actividad->responsible_name,
                ])->all(),
            'types' => $puedeAgregar ? Inertia::defer(fn () => ActivityType::where('active', true)->orderBy('position')->orderBy('name')->get(['id', 'name'])) : [],
            'canAdd' => $puedeAgregar,
        ]);
    }

    /** Sin aprobación (P-03): queda en firme al guardarla. Sus gastos sí siguen la cadena (RF-015). */
    public function storeActivity(StoreInternalActivityRequest $request, Schedule $schedule): RedirectResponse
    {
        $actividad = Activity::create([
            'site_id' => $schedule->site_id,
            'committee_id' => $schedule->committee_id,
            'schedule_id' => $schedule->id,
            'title' => $request->validated('title'),
            'theme' => $request->validated('theme'),
            'activity_type_id' => $request->validated('activity_type_id'),
            'scheduled_at' => $request->scheduledAt(),
            'ends_at' => $request->endsAt(),
            'location' => $request->validated('location'),
            'responsible_name' => $request->validated('responsible'),
            'status' => Activity::APPROVED,
            'proposed_by_user_id' => $request->user()->id,
            'proposed_at' => now(),
        ]);

        return redirect("/schedules/{$schedule->id}")->with('notice', "Listo. «{$actividad->title}» quedó en el cronograma. Las actividades internas no pasan por aprobación.");
    }
}
