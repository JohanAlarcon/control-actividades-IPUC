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

    /** El padrón lo lleva el pastor: solo él registra una ficha (RF-085, D-33). */
    public function create(User $quien): bool
    {
        return $this->esElPastor($quien);
    }

    /** Y solo él la corrige. Los cargos arman su lista, sin tocar los datos de nadie (CL-28). */
    public function update(User $quien, Member $miembro): bool
    {
        return $this->esElPastor($quien);
    }

    private function esElPastor(User $quien): bool
    {
        $scope = app(CurrentScope::class);

        return $scope->user()?->is($quien) === true && $scope->isPastor();
    }
}
