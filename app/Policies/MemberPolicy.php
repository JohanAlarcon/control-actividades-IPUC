<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\User;
use App\Support\Scope\CurrentScope;

class MemberPolicy
{
    /** La lista la abre cualquiera con cuenta: lo que ve lo decide el filtro de su cargo. */
    public function viewAny(User $quien): bool
    {
        return true;
    }

    /** Registra el pastor o cualquier cargo, en los comites de su alcance. */
    public function create(User $quien): bool
    {
        $scope = app(CurrentScope::class);

        return $scope->user()?->is($quien) === true && ($scope->isPastor() || $scope->activeRole() !== null);
    }

    /** Corrige quien la ve por su cargo. A quien no esta en ningun comite lo corrige el pastor (CL-03). */
    public function update(User $quien, Member $miembro): bool
    {
        $scope = app(CurrentScope::class);

        if ($scope->user()?->is($quien) !== true) {
            return false;
        }

        return $scope->isPastor() || ($scope->activeRole() !== null && $miembro->committee_id !== null && $scope->sees($miembro->committee_id));
    }
}
