<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActivityRequest;
use App\Models\Activity;
use App\Models\AnnualPlan;
use App\Support\Scope\CurrentScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/**
 * El plan del año (HU-08): los comites cargan sus actividades y el pastor lo aprueba completo, en
 * un solo acto, sin autorizar cada una por separado (RF-025). Lo que llega despues del plan va
 * como propuesta aparte, y se distingue (RF-024).
 */
class AnnualPlanController extends Controller
{
    /** Cargar una actividad al plan de este año o del siguiente, mientras no este aprobado. El año lo revisa el formulario. */
    public function storeActivity(StoreActivityRequest $request, CurrentScope $scope, int $year): RedirectResponse
    {
        $plan = AnnualPlan::firstOrCreate(['site_id' => $request->user()->site_id, 'year' => $year]);

        if ($plan->isApproved()) {
            throw ValidationException::withMessages(['plan' => "El plan {$year} ya está aprobado. Lo nuevo va como propuesta aparte."]);
        }

        $actividad = Activity::create($request->activityData($scope->activeRole()) + ['annual_plan_id' => $plan->id, 'status' => Activity::PENDING]);

        return redirect("/activities?year={$year}")
            ->with('notice', "Listo. «{$actividad->title}» quedó en el plan {$year}. Entra al cronograma cuando el pastor apruebe el plan completo.");
    }

    /**
     * El pastor aprueba el plan en un solo acto: queda una sola fila con su nombre y la fecha, y
     * todas las actividades cargadas quedan aprobadas (RF-025, CE-02).
     */
    public function approve(Request $request, int $year): RedirectResponse
    {
        Gate::authorize('is-pastor');

        $plan = AnnualPlan::where('year', $year)->first();

        if ($plan === null || ! $plan->activities()->where('status', Activity::PENDING)->exists()) {
            throw ValidationException::withMessages(['plan' => "El plan {$year} no tiene actividades cargadas para aprobar."]);
        }

        if ($plan->isApproved()) {
            throw ValidationException::withMessages(['plan' => "El plan {$year} ya está aprobado."]);
        }

        $aprobadas = DB::transaction(function () use ($plan, $request) {
            $plan->update(['approved_by_user_id' => $request->user()->id, 'approved_at' => now()]);

            return $plan->activities()
                ->where('status', Activity::PENDING)
                ->update(['status' => Activity::APPROVED, 'approved_by_user_id' => $request->user()->id, 'approved_at' => now()]);
        });

        return back()->with('notice', "Listo. El plan {$year} quedó aprobado: {$aprobadas} actividades entraron al cronograma.");
    }
}
