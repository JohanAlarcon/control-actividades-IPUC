<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSympathizerRequest;
use App\Models\Approval;
use App\Models\Committee;
use App\Models\Sympathizer;
use App\Models\SympathizerStage;
use App\Models\SympathizerVisit;
use App\Support\Approvals\ApprovalFlow;
use App\Support\Scope\CurrentScope;
use App\Support\Sympathizers\CommitmentLevel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Los simpatizantes (HU-13, HU-14). Registrar lo puede cualquier cargo; acompañarlos, solo el
 * pastor y quien preside el comite que los acompaña (RF-040, R-06).
 */
class SympathizerController extends Controller
{
    public function __construct(private readonly CurrentScope $scope) {}

    /**
     * Para quien acompaña: cuantos, como van y el embudo de las cinco etapas (HU-14, escenario 1).
     * Para los demas: la explicacion de por que no ven las fichas, sin que parezca un error.
     */
    public function index(): Response
    {
        if (Gate::denies('follow', Sympathizer::class)) {
            return Inertia::render('Sympathizers/Index', ['canFollow' => false, 'followers' => Committee::followingSympathizers()?->name]);
        }

        $personas = $this->conCompromiso()
            ->whereNull('converted_member_id')
            ->orderBy('name')
            ->get();
        $tarjetas = $personas->map($this->tarjeta(...));

        return Inertia::render('Sympathizers/Index', [
            'canFollow' => true,
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
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Sympathizer::class);

        return Inertia::render('Sympathizers/Form');
    }

    /**
     * Queda registrado, con quien lo registro como constancia (S-07), en la primera etapa desde su
     * primer contacto, y su seguimiento pasa al comite que acompaña (HU-13, escenario 1).
     */
    public function store(StoreSympathizerRequest $request): RedirectResponse
    {
        $comite = Committee::followingSympathizers();

        if ($comite === null) {
            throw ValidationException::withMessages(['name' => 'La congregación todavía no tiene un comité que acompañe a los simpatizantes. Hable con el pastor.']);
        }

        $persona = DB::transaction(function () use ($request, $comite) {
            $persona = Sympathizer::create($request->sympathizerData() + [
                'site_id' => $comite->site_id,
                'committee_id' => $comite->id,
                'invited_by_user_id' => $request->user()->id,
                'stage' => 1,
            ]);
            $persona->stages()->create([
                'site_id' => $persona->site_id,
                'stage' => 1,
                'reached_at' => $persona->first_contact_at,
                'recorded_by_user_id' => $request->user()->id,
            ]);

            return $persona;
        });

        $registrado = $persona->gender === 'f' ? 'registrada' : 'registrado';

        if (Gate::allows('follow', $persona)) {
            return redirect("/sympathizers/{$persona->id}")->with('notice', "Listo. {$persona->name} quedó {$registrado}.");
        }

        return redirect('/dashboard')->with('notice', "Listo. {$persona->name} quedó {$registrado}, y usted como quien lo invitó. "
            ."Su seguimiento lo lleva la presidencia del {$comite->name}; por eso usted no verá su ficha.");
    }

    /** La ficha: el camino con sus fechas, el compromiso y como se calculo (HU-14, escenarios 2 y 3). */
    public function show(int $sympathizer): Response
    {
        $persona = $this->conCompromiso()
            ->leftJoin('users as inviters', 'inviters.id', '=', 'sympathizers.invited_by_user_id')
            ->addSelect('inviters.name as invited_by_user_name')
            ->findOrFail($sympathizer);

        Gate::authorize('follow', $persona);

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

    private function conCompromiso(): Builder
    {
        return Sympathizer::query()
            ->select('sympathizers.*')
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
            'commitment' => CommitmentLevel::forSympathizer($persona),
            'promotionPending' => (bool) $persona->promotion_pending,
            'convertedAt' => $persona->converted_at?->toDateString(),
        ];
    }
}
