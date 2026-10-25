<?php

namespace App\Http\Middleware;

use App\Models\Approval;
use App\Support\Scope\CurrentScope;
use App\Support\Scope\HiddenCounts;
use Illuminate\Http\Request;
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
            ],
            'scope' => [
                'activeRole' => $this->cargoActivo($scope),
                'label' => $this->etiquetaDelAmbito($scope),
                'mustChoose' => $scope->mustChoose(),
                'canSwitch' => $scope->canSwitch(),
            ],
            // Diferidas: no viajan en la primera carga de ninguna pantalla,
            // para que el bloque compartido no se coma el presupuesto (A-3).
            'scope.hidden' => Inertia::defer(fn () => $this->conteosOcultos($scope)),
            'pendingCount' => Inertia::defer(fn () => $this->pendientes($scope)),
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

    private function pendientes(CurrentScope $scope): int
    {
        if (! $scope->isActive()) {
            return 0;
        }

        return Approval::where('status', Approval::PENDING)->count();
    }
}
