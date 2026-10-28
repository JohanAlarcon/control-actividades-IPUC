<?php

namespace App\Policies;

use App\Models\CommitteeUser;
use App\Models\Sympathizer;
use App\Models\User;
use App\Support\Scope\CurrentScope;

/**
 * Los simpatizantes (RF-040, D-34). Registrarlos y acompañarlos lo hace cualquier cargo; cuales ve
 * cada uno lo decide el filtro: BIS, todos; un comite, los asignados a su ambito. Reasignar es de
 * BIS. El paso a miembro lo proponen BIS o el presidente del comite asignado, y lo aprueba el pastor.
 */
class SympathizerPolicy
{
    public function create(User $quien): bool
    {
        return $this->conCargo($quien);
    }

    public function follow(User $quien, ?Sympathizer $persona = null): bool
    {
        return $this->conCargo($quien);
    }

    /** Reasignar es de BIS (RF-097); el pastor, que ve todo, tambien puede. */
    public function assign(User $quien, ?Sympathizer $persona = null): bool
    {
        $scope = app(CurrentScope::class);

        return $scope->user()?->is($quien) === true && ($scope->isPastor() || $scope->isReception());
    }

    /** Cuando la persona recorrio las cinco etapas (HU-14 escenario 6, RN-06). */
    public function promote(User $quien, Sympathizer $persona): bool
    {
        $scope = app(CurrentScope::class);
        $cargo = $scope->activeRole();
        $proponer = $scope->isReception() || ($cargo?->role === CommitteeUser::PRESIDENT && $scope->sees($persona->committee_id));

        return $scope->user()?->is($quien) === true
            && $proponer
            && $persona->stage === Sympathizer::LAST_STAGE
            && $persona->converted_member_id === null;
    }

    private function conCargo(User $quien): bool
    {
        $scope = app(CurrentScope::class);

        return $scope->user()?->is($quien) === true && ($scope->isPastor() || $scope->activeRole() !== null);
    }
}
