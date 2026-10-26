<?php

namespace App\Http\Middleware;

use App\Models\Approval;
use App\Models\Sympathizer;
use App\Support\Approvals\ApprovalCards;
use App\Support\Scope\CurrentScope;
use App\Support\Scope\HiddenCounts;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    private ?Collection $pendientes = null;

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $scope = app(CurrentScope::class);
        $usuario = $request->user();

        return array_merge(parent::share($request), [
            'name' => config('app.name'),
            'flash' => [
                'notice' => fn () => $request->session()->get('notice'),
                'temporaryPassword' => fn () => $request->session()->get('temporaryPassword'),
            ],
            'auth' => [
                'user' => $usuario ? [
                    'id' => $usuario->id,
                    'name' => $usuario->name,
                    'username' => $usuario->username,
                    'isPastor' => $usuario->is_pastor,
                ] : null,
                'roles' => $this->cargosVigentes($scope),
                'followsSympathizers' => $usuario !== null && Gate::forUser($usuario)->allows('follow', Sympathizer::class),
            ],
            'scope' => [
                'activeRole' => $this->cargoActivo($scope),
                'label' => $this->etiquetaDelAmbito($scope),
                'mustChoose' => $scope->mustChoose(),
                'canSwitch' => $scope->canSwitch(),
            ],
            // Diferidas: no viajan en la primera carga de ninguna pantalla,
            // para que el bloque compartido no se coma el presupuesto (A-3).
            // En primer nivel y no dentro de «scope»: el navegador mezcla solo el primer
            // nivel, y una llave anidada reemplazaria «scope» entero al llegar.
            'scopeHidden' => Inertia::defer(fn () => $this->conteosOcultos($scope)),
            'pendingCount' => Inertia::defer(fn () => $this->pendientes($scope)->count()),
            'pendingSince' => Inertia::defer(fn () => $this->pendientes($scope)->min()?->toIso8601String()),
        ]);
    }

    private function cargosVigentes(CurrentScope $scope): array
    {
        if (! $scope->isActive() || $scope->isPastor()) {
            return [];
        }

        return $scope->roles()
            ->map(fn ($cargo) => [
                'id' => $cargo->id,
                'committeeId' => $cargo->committee_id,
                'committeeName' => $cargo->committee?->name,
                'role' => $cargo->role,
                'depth' => $cargo->committee?->depth,
            ])->all();
    }

    private function cargoActivo(CurrentScope $scope): ?array
    {
        $cargo = $scope->activeRole();

        if (! $cargo) {
            return null;
        }

        return [
            'id' => $cargo->id,
            'committeeId' => $cargo->committee_id,
            'committeeName' => $cargo->committee?->name,
            'role' => $cargo->role,
        ];
    }

    private function etiquetaDelAmbito(CurrentScope $scope): ?string
    {
        if (! $scope->isActive()) {
            return null;
        }

        if ($scope->isPastor()) {
            return 'Toda la congregación';
        }

        return $scope->activeRole()?->committee?->name;
    }

    private function conteosOcultos(CurrentScope $scope): array
    {
        return (new HiddenCounts($scope))->all();
    }

    /**
     * Lo que espera la decision del cargo activo (RF-055): el pastor decide lo de su
     * nivel y un presidente lo del suyo. Un lider, un secretario o un tesorero no
     * deciden solicitudes, asi que para ellos no hay nada esperando.
     */
    /**
     * Desde cuando espera cada solicitud que le toca decidir al cargo activo. Una sola consulta da
     * el numero del menu y la fecha de la mas antigua (HU-06, escenario 4).
     */
    private function pendientes(CurrentScope $scope): Collection
    {
        if ($this->pendientes !== null) {
            return $this->pendientes;
        }

        $nivel = ApprovalCards::levelDecidedBy($scope);

        return $this->pendientes = $nivel === null ? collect() : Approval::where('status', Approval::PENDING)
            ->where('level', $nivel)
            ->get(['requested_at', 'escalated_at'])
            ->map(fn (Approval $solicitud) => $solicitud->escalated_at ?? $solicitud->requested_at);
    }
}
