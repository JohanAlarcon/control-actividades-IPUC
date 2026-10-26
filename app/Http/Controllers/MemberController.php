<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveMemberRequest;
use App\Models\Member;
use App\Support\Committees\CommitteeOptions;
use App\Support\Scope\CurrentScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/** Los miembros del alcance del cargo (HU-10). */
class MemberController extends Controller
{
    /** Los rangos de edad del filtro (RF-035). La edad sale de la fecha o de la edad anotada (RF-031). */
    private const AGE_RANGES = ['children' => [0, 12], 'teens' => [13, 17], 'young' => [18, 29], 'adults' => [30, 59], 'seniors' => [60, 150]];

    public function __construct(private readonly CurrentScope $scope) {}

    /**
     * La lista con sus filtros combinables y «cuántos cumplen sobre el total» (RF-035). Una sola
     * consulta trae a todos los del alcance; los filtros, la edad y los conteos se sacan de ahí,
     * así que el número de consultas no crece con los filtros ni con los datos.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Member::class);

        $filtros = [
            'committee' => $request->integer('committee') ?: null,
            'gender' => in_array($request->query('gender'), ['f', 'm'], true) ? $request->query('gender') : null,
            'marital' => in_array($request->query('marital'), Member::MARITAL_STATUSES, true) ? $request->query('marital') : null,
            'baptized' => in_array($request->query('baptized'), ['yes', 'no'], true) ? $request->query('baptized') : null,
            'age' => array_key_exists((string) $request->query('age'), self::AGE_RANGES) ? $request->query('age') : null,
        ];

        $todos = Member::query()
            ->leftJoin('committees', 'committees.id', '=', 'members.committee_id')
            ->leftJoin('committees as parents', 'parents.id', '=', 'committees.parent_id')
            ->where('members.active', true)
            ->orderBy('members.name')
            ->get(['members.*', 'committees.name as committee_name', 'committees.parent_id as committee_parent_id', 'parents.parent_id as committee_grandparent_id']);

        return Inertia::render('Members/Index', [
            'members' => $todos->filter(fn (Member $miembro) => $this->cumple($miembro, $filtros))->map($this->tarjeta(...))->values(),
            'filters' => $filtros,
            'totalCount' => $todos->count(),
            'missingConsent' => $todos->where('data_consent', false)->count(),
            'pendingProfiles' => $todos->where('profile_pending', true)->count(),
            'canCreate' => Gate::allows('create', Member::class),
            'committees' => Inertia::defer(fn () => CommitteeOptions::forScope()),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Member::class);

        return Inertia::render('Members/Form', [
            'member' => null,
            'committees' => CommitteeOptions::forScope(),
            'canLeaveWithoutCommittee' => $this->scope->isPastor(),
        ]);
    }

    public function store(SaveMemberRequest $request): RedirectResponse
    {
        $miembro = Member::create($request->memberData() + ['site_id' => $request->user()->site_id]);
        $registrado = $miembro->gender === 'f' ? 'registrada' : 'registrado';
        $edad = $miembro->age();

        return redirect('/members')->with('notice', "Listo. {$miembro->name} quedó {$registrado}. "
            .($edad === null ? 'Su edad queda por completar.' : "Tiene {$edad} años."));
    }

    public function edit(Member $member): Response
    {
        Gate::authorize('update', $member);

        return Inertia::render('Members/Form', [
            'member' => [
                'id' => $member->id,
                'name' => $member->name,
                'ageMode' => $member->birth_date === null && $member->age_reported !== null ? 'age' : 'date',
                'birthDate' => $member->birth_date?->toDateString(),
                'ageReported' => $member->age_reported,
                'age' => $member->age(),
                'gender' => $member->gender,
                'maritalStatus' => $member->marital_status,
                'adultConfirmed' => $member->adult_confirmed,
                'baptized' => $member->baptized,
                'baptizedAt' => $member->baptized_at?->toDateString(),
                'joinedAt' => $member->joined_at?->toDateString(),
                'committeeId' => $member->committee_id,
                'phone' => $member->phone,
                'neighborhood' => $member->neighborhood,
                'dataConsent' => $member->data_consent,
                'dataConsentAt' => $member->data_consent_at?->toDateString(),
                'dataConsentMeans' => $member->data_consent_means,
            ],
            'committees' => CommitteeOptions::forScope(),
            'canLeaveWithoutCommittee' => $this->scope->isPastor(),
        ]);
    }

    public function update(SaveMemberRequest $request, Member $member): RedirectResponse
    {
        $member->update($request->memberData($member));

        return redirect('/members')->with('notice', "Listo. Los datos de {$member->name} quedaron guardados.");
    }

    private function cumple(Member $miembro, array $filtros): bool
    {
        $edad = $miembro->age();
        $rango = $filtros['age'] ? self::AGE_RANGES[$filtros['age']] : null;

        return (! $filtros['committee'] || in_array($filtros['committee'], [$miembro->committee_id, $miembro->committee_parent_id, $miembro->committee_grandparent_id], true))
            && (! $filtros['gender'] || $miembro->gender === $filtros['gender'])
            && (! $filtros['marital'] || $miembro->marital_status === $filtros['marital'])
            && (! $filtros['baptized'] || $miembro->baptized === ($filtros['baptized'] === 'yes'))
            && (! $rango || ($edad !== null && $edad >= $rango[0] && $edad <= $rango[1]));
    }

    /** Lo que muestra la tarjeta de un miembro, y las señales de lo que le falta a su ficha. */
    private function tarjeta(Member $miembro): array
    {
        return [
            'id' => $miembro->id,
            'name' => $miembro->name,
            'age' => $miembro->age(),
            'committee' => $miembro->committee_name,
            'gender' => $miembro->gender,
            'maritalStatus' => $miembro->marital_status,
            'baptized' => $miembro->baptized,
            'phone' => $miembro->phone,
            'neighborhood' => $miembro->neighborhood,
            'dataConsent' => $miembro->data_consent,
            'profilePending' => $miembro->profile_pending,
        ];
    }
}
