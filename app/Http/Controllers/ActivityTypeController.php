<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActivityTypeRequest;
use App\Http\Requests\UpdateActivityTypeRequest;
use App\Models\ActivityType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/** Las clases de actividad de la congregacion. Solo el pastor las administra (D-25). */
class ActivityTypeController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('is-pastor');

        return Inertia::render('ActivityTypes/Index', [
            'types' => ActivityType::query()
                ->withCount('activities')
                ->orderBy('position')
                ->orderBy('name')
                ->get(['id', 'name', 'active'])
                ->map(fn (ActivityType $clase) => [
                    'id' => $clase->id,
                    'name' => $clase->name,
                    'active' => $clase->active,
                    'uses' => $clase->activities_count,
                ]),
        ]);
    }

    public function store(StoreActivityTypeRequest $request): RedirectResponse
    {
        $clase = ActivityType::create([
            'site_id' => $request->user()->site_id,
            'name' => $request->validated('name'),
            'position' => (int) ActivityType::max('position') + 1,
        ]);

        return back()->with('notice', "Listo. La clase «{$clase->name}» quedó agregada y ya se ofrece al proponer.");
    }

    /** Cambiar el nombre, o desactivarla y volverla a activar. Nunca se borra: tiene historia. */
    public function update(UpdateActivityTypeRequest $request, ActivityType $activityType): RedirectResponse
    {
        $activityType->update($request->validated());

        $aviso = match (true) {
            $request->has('name') => "Listo. Ahora se llama «{$activityType->name}».",
            $activityType->active => "Listo. «{$activityType->name}» se ofrece de nuevo al proponer.",
            default => "Listo. «{$activityType->name}» ya no se ofrece al proponer. Las actividades que la tienen no cambian.",
        };

        return back()->with('notice', $aviso);
    }
}
