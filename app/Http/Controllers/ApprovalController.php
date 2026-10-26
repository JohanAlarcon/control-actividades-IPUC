<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkApproveRequest;
use App\Http\Requests\RejectApprovalRequest;
use App\Models\Approval;
use App\Support\Approvals\ApprovalCards;
use App\Support\Approvals\ApprovalFlow;
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

        $pendientes = ApprovalCards::query()
            ->where('approvals.status', Approval::PENDING)
            ->get()
            ->sortBy(fn (Approval $solicitud) => $solicitud->escalated_at ?? $solicitud->requested_at);

        [$porResolver, $enEspera] = $pendientes->partition(fn (Approval $solicitud) => Gate::allows('resolve', $solicitud));

        $historia = ApprovalCards::query()
            ->where('approvals.status', '!=', Approval::PENDING)
            ->latest('approvals.resolved_at')
            ->limit(self::HISTORY_SIZE)
            ->get();

        return Inertia::render('Approvals/Index', [
            'inbox' => $porResolver->map(ApprovalCards::card(...))->values(),
            'escalated' => $enEspera->map(ApprovalCards::card(...))->values(),
            'history' => $historia->map(ApprovalCards::card(...)),
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
}
