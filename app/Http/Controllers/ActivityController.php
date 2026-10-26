<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActivityRequest;
use App\Models\Activity;
use App\Models\ActivityTask;
use App\Models\ActivityType;
use App\Models\AnnualPlan;
use App\Models\Approval;
use App\Models\Committee;
use App\Support\Activities\ActivityStatus;
use App\Support\Activities\ScheduleCards;
use App\Support\Activities\ScheduleSummary;
use App\Support\Approvals\ApprovalCards;
use App\Support\Approvals\ApprovalFlow;
use App\Support\Scope\CurrentScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ActivityController extends Controller
{
    /** Los estados por los que se puede filtrar el cronograma. Lo no aprobado no aparece en el. */
    private const STATUS_FILTERS = [Activity::PENDING, Activity::APPROVED, Activity::IN_PROGRESS, Activity::COMPLETED];

    /** Cuantos años atras se puede consultar. El que viene se ve para cargar su plan (CL-13). */
    private const YEARS_BACK = 5;

    public function __construct(
        private readonly CurrentScope $scope,
        private readonly ApprovalFlow $flujo,
    ) {}

    /**
     * El cronograma del año (HU-09): lo propio completo y lo ajeno con los siete datos que se
     * comparten (RF-010). Los totales y el cumplimiento se recalculan con el filtro (RF-030). El
     * plan del año y las opciones de comite llegan aparte, para que la pantalla quepa en su
     * presupuesto de consultas.
     */
    public function index(Request $request): Response
    {
        $hoy = now(ScheduleCards::ZONE)->year;
        $anio = min(max($request->integer('year') ?: $hoy, $hoy - self::YEARS_BACK), $hoy + 1);
        $filtros = [
            'year' => $anio,
            'committee' => $request->integer('committee') ?: null,
            'month' => in_array($request->integer('month'), range(1, 12), true) ? $request->integer('month') : null,
            'status' => in_array($request->query('status'), self::STATUS_FILTERS, true) ? $request->query('status') : null,
        ];

        $tarjetas = $this->propias($anio, $filtros['committee'])
            ->concat($filtros['committee'] ? [] : $this->ajenas($anio))
            ->filter(fn (array $tarjeta) => ! $filtros['month'] || ScheduleCards::monthOf($tarjeta) === $filtros['month'])
            ->filter(fn (array $tarjeta) => ! $filtros['status'] || $tarjeta['status'] === $filtros['status'])
            ->sortBy('scheduledAt')
            ->values();

        return Inertia::render('Activities/Index', [
            'filters' => $filtros,
            'years' => ['from' => $hoy - self::YEARS_BACK, 'to' => $hoy + 1],
            'summary' => ScheduleSummary::of($tarjetas),
            'months' => ScheduleCards::byMonth($tarjetas, $filtros['month']),
            'canPropose' => Gate::allows('create', Activity::class),
            'annualPlan' => Inertia::defer(fn () => $this->plan($anio), 'plan'),
            'committees' => Inertia::defer(fn () => $this->opcionesDeComite(), 'committees'),
        ]);
    }

    /**
     * La ficha (HU-09, escenario 3). Lo propio trae todo; lo ajeno, solo lo que se comparte, y se
     * busca por el cronograma compartido: la unica salida del filtro, que solo lee (RF-010).
     */
    public function show(int $activity): Response
    {
        $actividad = ScheduleCards::ownQuery()
            ->leftJoin('users as proposers', 'proposers.id', '=', 'activities.proposed_by_user_id')
            ->leftJoin('users as completers', 'completers.id', '=', 'activities.completed_by_user_id')
            ->leftJoin('annual_plans', 'annual_plans.id', '=', 'activities.annual_plan_id')
            ->leftJoin('users as plan_approvers', 'plan_approvers.id', '=', 'annual_plans.approved_by_user_id')
            ->addSelect([
                'proposers.name as proposed_by_name',
                'completers.name as completed_by_name',
                'annual_plans.year as plan_year',
                'annual_plans.approved_at as plan_approved_at',
                'plan_approvers.name as plan_approved_by_name',
            ])
            ->withCasts(['plan_approved_at' => 'datetime'])
            ->where('activities.id', $activity)
            ->first();

        if ($actividad === null) {
            $ajena = Activity::forSharedCalendar()
                ->where('activities.id', $activity)
                ->where('activities.status', '!=', Activity::REJECTED)
                ->firstOrFail();

            return Inertia::render('Activities/Show', ['activity' => ScheduleCards::shared($ajena), 'mine' => false]);
        }

        $puedeEditar = Gate::allows('update', $actividad);

        return Inertia::render('Activities/Show', [
            'activity' => ScheduleCards::own($actividad) + [
                'proposedBy' => $actividad->proposed_by_name,
                'planYear' => $actividad->plan_year,
                'rejectionReason' => $actividad->rejection_reason,
            ],
            'mine' => true,
            'tasks' => fn () => $actividad->tasks()->orderBy('id')->get()->map(fn (ActivityTask $tarea) => [
                'id' => $tarea->id,
                'description' => $tarea->description,
                'assignedTo' => $tarea->assigned_to_name,
                'done' => $tarea->done,
                'doneAt' => $tarea->done_at?->toIso8601String(),
            ]),
            'canEdit' => $puedeEditar && ! in_array($actividad->status, [Activity::REJECTED, Activity::COMPLETED], true),
            'canCheckTasks' => $puedeEditar && ActivityStatus::acceptsTaskChecks($actividad),
            'trail' => Inertia::defer(fn () => $this->rastro($actividad)),
        ]);
    }

    /**
     * El formulario de propuesta. Con `?plan=2027` carga la actividad al plan de ese año, si
     * todavia no esta aprobado (HU-08); si no, dice desde antes a quien le va a llegar.
     */
    public function create(Request $request): Response
    {
        Gate::authorize('create', Activity::class);

        $plan = $this->planAbierto($request->integer('plan'));

        return Inertia::render('Activities/Form', [
            'recipient' => $plan ? null : $this->flujo->recipientFor($this->scope->activeRole())?->name,
            'types' => ActivityType::where('active', true)->orderBy('position')->orderBy('name')->get(['id', 'name']),
            'plan' => $plan,
        ]);
    }

    /**
     * La actividad queda propuesta y su solicitud, en manos de quien le toca (RF-016, RF-017). Las
     * dos cosas pasan juntas o ninguna: no queda una propuesta sin solicitud.
     */
    public function store(StoreActivityRequest $request): RedirectResponse
    {
        $cargo = $this->scope->activeRole();

        DB::transaction(function () use ($request, $cargo) {
            $actividad = Activity::create($request->activityData($cargo) + ['status' => Activity::PENDING]);

            $monto = $actividad->budget_amount === null ? null : (float) $actividad->budget_amount;
            $this->flujo->request($cargo, $actividad, Approval::TYPE_ACTIVITY, $actividad->title, $actividad->description, $monto);
        });

        $quien = $this->flujo->recipientFor($cargo)?->name;

        return redirect('/approvals')->with('notice', $quien
            ? "Listo. Su propuesta le llegó a {$quien}."
            : 'Listo. Su propuesta quedó registrada. Su comité todavía no tiene quién la revise: le llegará a la persona que el pastor nombre.');
    }

    /** Sin tareas, el responsable la marca cumplida, y queda su nombre (R-04). Con tareas se cumple sola. */
    public function complete(Request $request, Activity $activity): RedirectResponse
    {
        Gate::authorize('update', $activity);

        if (! ActivityStatus::canMarkCompleted($activity, $activity->tasks()->count())) {
            throw ValidationException::withMessages(['activity' => $activity->tasks()->exists()
                ? 'Esta actividad se cumple sola cuando se terminan todas sus tareas.'
                : 'Esta actividad no se puede marcar cumplida: todavía no está aprobada, o ya se cumplió.']);
        }

        $activity->update(['status' => Activity::COMPLETED, 'completed_at' => now(), 'completed_by_user_id' => $request->user()->id]);

        return back()->with('notice', 'Listo. La actividad quedó cumplida, con su nombre y la fecha.');
    }

    private function propias(int $anio, ?int $comite): Collection
    {
        return ScheduleCards::ownQuery()
            ->whereBetween('activities.scheduled_at', ScheduleCards::yearRange($anio))
            ->where('activities.status', '!=', Activity::REJECTED)
            ->get()
            ->filter(fn (Activity $actividad) => $comite === null
                || in_array($comite, [$actividad->committee_id, $actividad->committee_parent_id, $actividad->committee_grandparent_id], true))
            ->map(ScheduleCards::own(...));
    }

    /** Lo de los demas comites, por la unica salida del filtro. El pastor no la necesita: ya ve todo. */
    private function ajenas(int $anio): Collection
    {
        if ($this->scope->isPastor()) {
            return collect();
        }

        return Activity::forSharedCalendar()
            ->whereNotIn('activities.committee_id', $this->scope->committeeIds())
            ->whereBetween('activities.scheduled_at', ScheduleCards::yearRange($anio))
            ->where('activities.status', '!=', Activity::REJECTED)
            ->get()
            ->map(ScheduleCards::shared(...));
    }

    /** El plan del año: quien lo aprobo y cuando, o cuanto espera su aprobacion (HU-08). */
    private function plan(int $anio): array
    {
        $plan = AnnualPlan::query()
            ->leftJoin('users', 'users.id', '=', 'annual_plans.approved_by_user_id')
            ->where('annual_plans.year', $anio)
            ->first(['annual_plans.*', 'users.name as approver_name']);

        $aprobado = (bool) $plan?->isApproved();
        $cargadas = $plan && ! $aprobado ? Activity::where('annual_plan_id', $plan->id)->where('status', Activity::PENDING)->count() : 0;

        return [
            'year' => $anio,
            'approved' => $aprobado,
            'approvedBy' => $plan?->approver_name,
            'approvedAt' => $plan?->approved_at?->toIso8601String(),
            'waiting' => $cargadas,
            'canApprove' => $this->scope->isPastor() && ! $aprobado && $cargadas > 0,
            'canLoad' => $this->planAbierto($anio) !== null && Gate::allows('create', Activity::class),
        ];
    }

    /** El año del plan al que todavia se le pueden cargar actividades: el de este año o el siguiente, sin aprobar. */
    private function planAbierto(int $anio): ?int
    {
        $hoy = now(ScheduleCards::ZONE)->year;

        if (! in_array($anio, [$hoy, $hoy + 1], true)) {
            return null;
        }

        return AnnualPlan::where('year', $anio)->whereNotNull('approved_at')->exists() ? null : $anio;
    }

    /** Los comites del alcance, en orden de arbol: cada uno seguido de los que cuelgan de el. */
    private function opcionesDeComite(): array
    {
        $comites = Committee::query()->where('active', true)->orderBy('position')->orderBy('name')->get(['id', 'name', 'parent_id', 'depth']);
        $visibles = $comites->pluck('id')->all();

        $rama = function (Collection $nivel) use (&$rama, $comites): array {
            return $nivel->flatMap(fn (Committee $comite) => [
                ['id' => $comite->id, 'name' => $comite->name, 'depth' => $comite->depth],
                ...$rama($comites->where('parent_id', $comite->id)),
            ])->all();
        };

        return $rama($comites->filter(fn (Committee $comite) => ! in_array($comite->parent_id, $visibles, true)));
    }

    /**
     * El rastro de decisiones (principio III): quien la propuso, quien le dio el visto bueno, quien
     * la aprobo o por que no, y quien la dio por cumplida. Llega aparte, despues de la ficha.
     */
    private function rastro(Activity $actividad): array
    {
        $eventos = collect([[
            'date' => $actividad->proposed_at?->toIso8601String(),
            'text' => $actividad->plan_year
                ? "Cargada al plan {$actividad->plan_year} por {$actividad->proposed_by_name}"
                : "Propuesta por {$actividad->proposed_by_name}",
        ]]);

        ApprovalCards::query()
            ->where('approvals.approvable_type', $actividad->getMorphClass())
            ->where('approvals.approvable_id', $actividad->id)
            ->get()
            ->each(function (Approval $solicitud) use ($eventos) {
                if ($solicitud->escalated_at) {
                    $eventos->push(['date' => $solicitud->escalated_at->toIso8601String(), 'text' => "Visto bueno de {$solicitud->escalated_by_name}"]);
                }

                if ($solicitud->resolved_at) {
                    $eventos->push([
                        'date' => $solicitud->resolved_at->toIso8601String(),
                        'text' => match (true) {
                            $solicitud->status === Approval::APPROVED => "Aprobada por {$solicitud->resolved_by_name}",
                            $solicitud->level === Approval::LEVEL_PRESIDENT => "Devuelta por {$solicitud->resolved_by_name}",
                            default => "No aprobada por {$solicitud->resolved_by_name}",
                        },
                        'reason' => $solicitud->reason,
                    ]);
                }
            });

        if ($actividad->plan_approved_at) {
            $eventos->push([
                'date' => $actividad->plan_approved_at->toIso8601String(),
                'text' => "Aprobada con el plan {$actividad->plan_year} por {$actividad->plan_approved_by_name}",
            ]);
        }

        if ($actividad->completed_at) {
            $eventos->push([
                'date' => $actividad->completed_at->toIso8601String(),
                'text' => $actividad->completed_by_name
                    ? "Marcada cumplida por {$actividad->completed_by_name}"
                    : 'Cumplida: se terminaron todas sus tareas',
            ]);
        }

        return $eventos->filter(fn (array $evento) => $evento['date'] !== null)->sortBy('date')->values()->all();
    }
}
