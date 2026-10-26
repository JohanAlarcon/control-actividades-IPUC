<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkApproveRequest;
use App\Http\Requests\RejectApprovalRequest;
use App\Models\Activity;
use App\Models\Approval;
use App\Support\Approvals\ApprovalFlow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ApprovalController extends Controller
{
    /** Las decisiones ya tomadas que se muestran: las mas recientes, para que la lista no crezca sin fin. */
    private const HISTORY_SIZE = 20;

    public function index(): Response
    {
        Gate::authorize('viewAny', Approval::class);

        $pendientes = $this->solicitudes()
            ->where('approvals.status', Approval::PENDING)
            ->get()
            ->sortBy(fn (Approval $solicitud) => $solicitud->escalated_at ?? $solicitud->requested_at);

        [$porResolver, $enEspera] = $pendientes->partition(fn (Approval $solicitud) => Gate::allows('resolve', $solicitud));

        $historia = $this->solicitudes()
            ->where('approvals.status', '!=', Approval::PENDING)
            ->latest('approvals.resolved_at')
            ->limit(self::HISTORY_SIZE)
            ->get();

        return Inertia::render('Approvals/Index', [
            'inbox' => $porResolver->map($this->tarjeta(...))->values(),
            'escalated' => $enEspera->map($this->tarjeta(...))->values(),
            'history' => $historia->map($this->tarjeta(...)),
            'counts' => ['inbox' => $porResolver->count(), 'escalated' => $enEspera->count()],
        ]);
    }

    public function approve(Request $request, Approval $approval, ApprovalFlow $flujo): RedirectResponse
    {
        Gate::authorize('resolve', $approval);

        $solicitud = $flujo->approve($approval, $request->user());

        return back()->with('notice', $solicitud->isPending()
            ? 'Listo. Le dio su visto bueno, y ahora pasa al pastor, que es quien aprueba.'
            : 'Listo. La solicitud quedó aprobada.');
    }

    /** Varias de la misma clase, con una sola confirmacion del pastor (RF-022). Todas o ninguna. */
    public function bulkApprove(BulkApproveRequest $request, ApprovalFlow $flujo): RedirectResponse
    {
        $ids = $request->validated('ids');
        $solicitudes = Approval::whereKey($ids)->get();

        abort_if($solicitudes->count() !== count($ids), 404);
        $solicitudes->each(fn (Approval $solicitud) => Gate::authorize('resolve', $solicitud));

        $flujo->approveMany($solicitudes, $request->user());

        return back()->with('notice', "Listo. Quedaron aprobadas las {$solicitudes->count()} solicitudes, cada una con su propio registro.");
    }

    public function reject(RejectApprovalRequest $request, Approval $approval, ApprovalFlow $flujo): RedirectResponse
    {
        $solicitud = $flujo->reject($approval, $request->user(), $request->validated('reason'));

        return back()->with('notice', $solicitud->level === Approval::LEVEL_PRESIDENT
            ? 'Listo. La devolvió, y quien la pidió va a ver el motivo para corregirla.'
            : 'Listo. No se aprobó, y quien la pidió va a ver el motivo.');
    }

    /**
     * Las solicitudes que el cargo ve, con los nombres ya puestos: una consulta por lista, tenga
     * las que tenga. El filtro de alcance va sobre approvals; la actividad solo se cruza si es del
     * mismo comite de la solicitud, para que el cruce no traiga nada de afuera.
     */
    private function solicitudes(): Builder
    {
        return Approval::query()
            ->join('committees', 'committees.id', '=', 'approvals.committee_id')
            ->join('users as requester', 'requester.id', '=', 'approvals.requested_by_user_id')
            ->leftJoin('users as escalator', 'escalator.id', '=', 'approvals.escalated_by_user_id')
            ->leftJoin('users as resolver', 'resolver.id', '=', 'approvals.resolved_by_user_id')
            ->leftJoin('activities', fn (JoinClause $cruce) => $cruce
                ->on('activities.id', '=', 'approvals.approvable_id')
                ->on('activities.committee_id', '=', 'approvals.committee_id')
                ->where('approvals.approvable_type', (new Activity)->getMorphClass()))
            ->select([
                'approvals.*',
                'committees.name as committee_name',
                'requester.name as requested_by_name',
                'escalator.name as escalated_by_name',
                'resolver.name as resolved_by_name',
                'activities.scheduled_at as activity_date',
                'activities.location as activity_location',
            ])
            ->withCasts(['activity_date' => 'datetime']);
    }

    /**
     * Lo que la tarjeta dice sin abrir nada (T068): de donde viene, quien la pidio, por quien ya
     * paso, cuanto cuesta, que mueve en el cronograma y desde cuando espera.
     */
    private function tarjeta(Approval $solicitud): array
    {
        return [
            'id' => $solicitud->id,
            'type' => $solicitud->type,
            'title' => $solicitud->title,
            'detail' => $solicitud->detail,
            'amount' => $solicitud->amount === null ? null : (float) $solicitud->amount,
            'committee' => $solicitud->committee_name,
            'requestedBy' => $solicitud->requested_by_name,
            'escalatedBy' => $solicitud->escalated_by_name,
            'level' => $solicitud->level,
            'status' => $solicitud->status,
            'waitingSince' => ($solicitud->escalated_at ?? $solicitud->requested_at)->toIso8601String(),
            'resolvedBy' => $solicitud->resolved_by_name,
            'resolvedAt' => $solicitud->resolved_at?->toIso8601String(),
            'reason' => $solicitud->reason,
            'activity' => $solicitud->activity_date
                ? ['date' => $solicitud->activity_date->toIso8601String(), 'location' => $solicitud->activity_location]
                : null,
        ];
    }
}
