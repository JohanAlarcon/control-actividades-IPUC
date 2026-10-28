<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSympathizerRequest;
use App\Models\Approval;
use App\Models\Committee;
use App\Models\Sympathizer;
use App\Models\SympathizerAssignment;
use App\Models\SympathizerStage;
use App\Models\SympathizerVisit;
use App\Support\Approvals\ApprovalFlow;
use App\Support\Scope\CurrentScope;
use App\Support\Sympathizers\Assignments;
use App\Support\Sympathizers\CommitmentLevel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Los simpatizantes (HU-13, HU-14). BIS los recibe, los ve a todos y los asigna al comite que los
 * acompaña; cada comite registra a sus amigos y acompaña a los asignados a su ambito (RF-040, D-34).
 */
class SympathizerController extends Controller
{
    public function __construct(private readonly CurrentScope $scope) {}

    /**
     * Cuantos, como van y el embudo de las cinco etapas (HU-14, escenario 1): en BIS y para el
     * pastor, de toda la sede y por comite asignado; en un comite, solo los suyos.
     */
    public function index(): Response
    {
        Gate::authorize('follow', Sympathizer::class);

        $personas = $this->conCompromiso()
            ->whereNull('converted_member_id')
            ->orderBy('name')
            ->get();
        $tarjetas = $personas->map($this->tarjeta(...));
        $deTodos = $this->scope->isReception() || $this->scope->isPastor();

        return Inertia::render('Sympathizers/Index', [
            'isReception' => $this->scope->isReception(),
            'stats' => [
                'total' => $tarjetas->count(),
                'growing' => $tarjetas->where('commitment.level', CommitmentLevel::GROWING)->count(),
                'stable' => $tarjetas->where('commitment.level', CommitmentLevel::STABLE)->count(),
                'drifting' => $tarjetas->where('commitment.level', CommitmentLevel::DRIFTING)->count(),
            ],
            'funnel' => collect(range(1, Sympathizer::LAST_STAGE))
                ->map(fn (int $etapa) => ['stage' => $etapa, 'count' => $tarjetas->where('stage', '>=', $etapa)->count()])
                ->all(),
            'list' => $tarjetas->values(),
            'missingConsent' => $personas->where('data_consent', false)->count(),
            'byCommittee' => $deTodos ? $tarjetas->groupBy('committee')
                ->map(fn ($grupo, string $comite) => [
                    'committee' => $comite,
                    'total' => $grupo->count(),
                    'drifting' => $grupo->where('commitment.level', CommitmentLevel::DRIFTING)->count(),
                ])
                ->sortBy('committee')
                ->values() : null,
        ]);
    }

    /** BIS y el pastor escogen el comite que la acompañará, de la lista de la excepcion 2 (D-34). */
    public function create(): Response
    {
        Gate::authorize('create', Sympathizer::class);
        $escoge = $this->scope->isReception() || $this->scope->isPastor();

        return Inertia::render('Sympathizers/Form', [
            'committees' => $escoge ? Committee::forAssignment() : null,
            'ownCommittee' => $escoge ? null : $this->scope->activeRole()?->committee?->name,
        ]);
    }

    /**
     * Queda registrado, con quien lo registro como constancia (S-07), en la primera etapa desde su
     * primer contacto, y asignado a un solo comite: el que escogio BIS, o el que lo registro (HU-13,
     * escenarios 1 y 3). La asignacion queda escrita (RF-097).
     */
    public function store(StoreSympathizerRequest $request): RedirectResponse
    {
        $comite = $request->assignedCommittee();

        $persona = DB::transaction(function () use ($request, $comite) {
            $persona = Sympathizer::create($request->sympathizerData() + [
                'site_id' => $request->user()->site_id,
                'committee_id' => $comite,
                'invited_by_user_id' => $request->user()->id,
                'stage' => 1,
            ]);
            Assignments::assign($persona, $comite, $request->user());
            $persona->stages()->create([
                'site_id' => $persona->site_id,
                'stage' => 1,
                'reached_at' => $persona->first_contact_at,
                'recorded_by_user_id' => $request->user()->id,
            ]);

            return $persona;
        });

        $registrado = $persona->gender === 'f' ? 'registrada' : 'registrado';

        return redirect("/sympathizers/{$persona->id}")->with('notice', "Listo. {$persona->name} quedó {$registrado} y la acompaña {$this->nombreDelComite($comite)}.");
    }

    /** La ficha: el camino con sus fechas, el compromiso y como se calculo (HU-14, escenarios 2 y 3). */
    public function show(int $sympathizer): Response
    {
        $persona = $this->conCompromiso()
            ->leftJoin('users as inviters', 'inviters.id', '=', 'sympathizers.invited_by_user_id')
            ->addSelect('inviters.name as invited_by_user_name')
            ->findOrFail($sympathizer);

        Gate::authorize('follow', $persona);
        $puedeAsignar = Gate::allows('assign', $persona);

        return Inertia::render('Sympathizers/Show', [
            'sympathizer' => $this->tarjeta($persona) + [
                'gender' => $persona->gender,
                'arrivalSource' => $persona->arrival_source,
                'invitedBy' => $persona->invited_by_name ?: $persona->invited_by_user_name,
                'firstContactAt' => $persona->first_contact_at->toDateString(),
                'dataConsent' => $persona->data_consent,
            ],
            'stages' => $persona->stages()
                ->leftJoin('users', 'users.id', '=', 'sympathizer_stages.recorded_by_user_id')
                ->orderBy('stage')
                ->get(['sympathizer_stages.stage', 'sympathizer_stages.reached_at', 'users.name as recorded_by_name'])
                ->map(fn (SympathizerStage $etapa) => ['stage' => $etapa->stage, 'reachedAt' => $etapa->reached_at->toDateString(), 'recordedBy' => $etapa->recorded_by_name]),
            'thresholds' => [
                'recentDays' => CommitmentLevel::RECENT_DAYS,
                'growingContacts' => CommitmentLevel::GROWING_MIN_CONTACTS,
                'growingDays' => CommitmentLevel::GROWING_MAX_DAYS,
                'driftingDays' => CommitmentLevel::DRIFTING_DAYS,
            ],
            'canPromote' => Gate::allows('promote', $persona) && ! $persona->promotion_pending,
            'canAssign' => $puedeAsignar,
            'committees' => $puedeAsignar ? Inertia::defer(fn () => Committee::forAssignment(), 'assign') : null,
            'assignments' => Inertia::defer(fn () => SympathizerAssignment::query()
                ->join('committees', 'committees.id', '=', 'sympathizer_assignments.committee_id')
                ->leftJoin('users', 'users.id', '=', 'sympathizer_assignments.assigned_by_user_id')
                ->where('sympathizer_assignments.sympathizer_id', $persona->id)
                ->orderBy('sympathizer_assignments.assigned_at')
                ->orderBy('sympathizer_assignments.id')
                ->get(['sympathizer_assignments.assigned_at', 'committees.name as committee_name', 'users.name as by_name'])
                ->map(fn (SympathizerAssignment $asignacion) => [
                    'committee' => $asignacion->committee_name,
                    'by' => $asignacion->by_name,
                    'at' => $asignacion->assigned_at->toIso8601String(),
                ]), 'history'),
            'visits' => Inertia::defer(fn () => SympathizerVisit::query()
                ->leftJoin('users', 'users.id', '=', 'sympathizer_visits.by_user_id')
                ->where('sympathizer_visits.sympathizer_id', $persona->id)
                ->orderByDesc('sympathizer_visits.visited_at')
                ->orderByDesc('sympathizer_visits.id')
                ->get(['sympathizer_visits.*', 'users.name as by_name'])
                ->map(fn (SympathizerVisit $visita) => [
                    'id' => $visita->id,
                    'visitedAt' => $visita->visited_at->toDateString(),
                    'type' => $visita->type,
                    'by' => $visita->by_name,
                    'note' => $visita->note,
                ])),
        ]);
    }

    /** Propone el paso a miembro: va al pastor, y solo con su aprobacion entra a la lista (RF-019, RN-06). */
    public function promote(Sympathizer $sympathizer, ApprovalFlow $flujo): RedirectResponse
    {
        Gate::authorize('promote', $sympathizer);

        if ($sympathizer->approvals()->where('status', Approval::PENDING)->exists()) {
            throw ValidationException::withMessages(['promote' => 'Ya hay una solicitud esperando al pastor para que pase a ser miembro.']);
        }

        $flujo->request($this->scope->activeRole(), $sympathizer, Approval::TYPE_NEW_MEMBER, "Pasar a miembro: {$sympathizer->name}");

        return back()->with('notice', "Listo. La solicitud para que {$sympathizer->name} pase a ser miembro le llegó al pastor.");
    }

    /** El nombre del comite asignado, sin salir del filtro: el del cargo, o el de la lista para asignar. */
    private function nombreDelComite(int $comite): string
    {
        $cargo = $this->scope->activeRole();

        return $cargo?->committee_id === $comite ? $cargo->committee->name : (string) Committee::forAssignment()->firstWhere('id', $comite)['name'];
    }

    private function conCompromiso(): Builder
    {
        return Sympathizer::query()
            ->leftJoin('committees', 'committees.id', '=', 'sympathizers.committee_id')
            ->select(['sympathizers.*', 'committees.name as committee_name'])
            ->withCommitment()
            ->withExists(['approvals as promotion_pending' => fn (Builder $solicitudes) => $solicitudes->where('status', Approval::PENDING)]);
    }

    private function tarjeta(Sympathizer $persona): array
    {
        return [
            'id' => $persona->id,
            'name' => $persona->name,
            'age' => $persona->age(),
            'phone' => $persona->phone,
            'neighborhood' => $persona->neighborhood,
            'stage' => $persona->stage,
            'committee' => $persona->committee_name,
            'commitment' => CommitmentLevel::forSympathizer($persona),
            'promotionPending' => (bool) $persona->promotion_pending,
            'convertedAt' => $persona->converted_at?->toDateString(),
        ];
    }
}
