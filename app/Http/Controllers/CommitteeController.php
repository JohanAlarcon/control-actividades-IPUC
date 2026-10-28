<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Support\Committees\SubtreeResolver;
use App\Support\Scope\CurrentScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Como esta organizada la congregacion (HU-03). Cada cargo ve su comite y lo que cuelga
 * de el. De lo ajeno no viaja nada: solo cuantos comites quedan fuera, en scopeHidden.
 */
class CommitteeController extends Controller
{
    public function __construct(
        private readonly CurrentScope $scope,
        private readonly SubtreeResolver $subtree,
    ) {}

    public function index(): Response
    {
        Gate::authorize('viewAny', Committee::class);

        $comites = Committee::query()
            ->where('active', true)
            ->orderBy('position')
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name', 'depth']);

        $comiteQueRevisa = $this->comiteQueRevisa();
        $titulares = $this->titulares($comites->pluck('id')->push($comiteQueRevisa)->filter()->all());

        return Inertia::render('Committees/Index', [
            'tree' => $this->arbol($comites, $titulares),
            'reviewer' => $comiteQueRevisa ? $this->nombreDe($titulares, $comiteQueRevisa, CommitteeUser::PRESIDENT) : null,
        ]);
    }

    public function show(int $committee): Response
    {
        // El comite y sus hijos salen en una sola consulta, para no pasarse de cinco. La
        // consulta pasa por el filtro del cargo: si es ajeno o no existe, no vuelve nada.
        $familia = Committee::query()
            ->leftJoin('users as editores', 'editores.id', '=', 'committees.identity_updated_by_user_id')
            ->where(fn ($consulta) => $consulta->whereKey($committee)->orWhere('committees.parent_id', $committee))
            ->orderBy('committees.position')
            ->orderBy('committees.name')
            ->get([
                'committees.id', 'committees.parent_id', 'committees.name', 'committees.depth', 'committees.active',
                'committees.logo_path', 'committees.motto', 'committees.objectives', 'committees.identity_updated_at',
                'editores.name as identity_updated_by_name',
            ]);

        $nodo = $familia->firstWhere('id', $committee) ?? abort(404);
        Gate::authorize('view', $nodo);

        $hijos = $familia->where('parent_id', $nodo->id)->where('active', true)->values();
        $titulares = $this->titulares($familia->pluck('id')->all());

        return Inertia::render('Committees/Show', [
            'committee' => [
                'id' => $nodo->id,
                'name' => $nodo->name,
                'depth' => $nodo->depth,
                'active' => $nodo->active,
            ],
            'identity' => [
                'logoUrl' => $nodo->logoUrl(),
                'motto' => $nodo->motto,
                'objectives' => $nodo->objectives,
                'updatedBy' => $nodo->identity_updated_by_name,
                'updatedAt' => $nodo->identity_updated_at?->toIso8601String(),
            ],
            'canEditIdentity' => Gate::allows('updateIdentity', $nodo),
            'responsible' => $this->responsableDe($nodo, $titulares),
            'secretary' => $this->nombreDe($titulares, $nodo->id, CommitteeUser::SECRETARY),
            'treasurer' => $this->nombreDe($titulares, $nodo->id, CommitteeUser::TREASURER),
            'hasChildren' => $hijos->isNotEmpty(),
            'children' => $hijos->map(fn (Committee $hijo) => [
                'id' => $hijo->id,
                'name' => $hijo->name,
                'depth' => $hijo->depth,
                'responsible' => $this->responsableDe($hijo, $titulares),
            ])->all(),
        ]);
    }

    /**
     * Para un lider, el comite de primer nivel cuyo presidente revisa lo que propone
     * (HU-03, escenario 4). El pastor y los presidentes no tienen a quien mostrar.
     */
    private function comiteQueRevisa(): ?int
    {
        $comite = $this->scope->activeRole()?->committee;

        return $comite && ! $comite->isTopLevel() ? $this->subtree->rootIdOf($comite) : null;
    }

    /**
     * Quien tiene hoy cada cargo de esos comites, con su nombre, en una sola consulta.
     * Quien tiene el acceso retirado no aparece: ya no puede responder por el comite.
     */
    private function titulares(array $ids): Collection
    {
        return CommitteeUser::query()
            ->current()
            ->whereIn('committee_user.committee_id', $ids)
            ->join('users', 'users.id', '=', 'committee_user.user_id')
            ->where('users.active', true)
            ->get(['committee_user.committee_id', 'committee_user.role', 'users.name'])
            ->groupBy('committee_id');
    }

    private function responsableDe(Committee $comite, Collection $titulares): ?string
    {
        $cargo = $comite->isTopLevel() ? CommitteeUser::PRESIDENT : CommitteeUser::LEADER;

        return $this->nombreDe($titulares, $comite->id, $cargo);
    }

    private function nombreDe(Collection $titulares, int $comiteId, string $cargo): ?string
    {
        return $titulares->get($comiteId)?->firstWhere('role', $cargo)?->name;
    }

    /** Cada comite con sus hijos adentro, empezando por los que no tienen padre a la vista. */
    private function arbol(Collection $comites, Collection $titulares): array
    {
        $visibles = $comites->pluck('id')->all();

        return $comites
            ->filter(fn (Committee $comite) => ! in_array($comite->parent_id, $visibles))
            ->map(fn (Committee $comite) => $this->nodo($comite, $comites, $titulares))
            ->values()
            ->all();
    }

    private function nodo(Committee $comite, Collection $comites, Collection $titulares): array
    {
        return [
            'id' => $comite->id,
            'name' => $comite->name,
            'depth' => $comite->depth,
            'responsible' => $this->responsableDe($comite, $titulares),
            'children' => $comites
                ->where('parent_id', $comite->id)
                ->map(fn (Committee $hijo) => $this->nodo($hijo, $comites, $titulares))
                ->values()
                ->all(),
        ];
    }
}
