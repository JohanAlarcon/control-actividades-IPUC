<?php

namespace App\Policies;

use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\User;
use App\Support\Scope\CurrentScope;

/**
 * La tercera capa del filtro (principio I): aunque el filtro de los modelos ya deja
 * fuera lo ajeno, la ficha de un comite vuelve a preguntar antes de mostrarse.
 */
class CommitteePolicy
{
    /** La seccion la abre cualquiera con cuenta: lo que ve depende de su cargo. */
    public function viewAny(User $quien): bool
    {
        return true;
    }

    public function view(User $quien, Committee $comite): bool
    {
        $scope = app(CurrentScope::class);

        return $scope->user()?->is($quien) === true && $scope->sees($comite->id);
    }

    /**
     * La identidad la registra el presidente (RF-083): la de su comité y la de sus sub-comités. El
     * líder de un sub-comité la ve, pero no la cambia (HU-26 escenario 5).
     */
    public function updateIdentity(User $quien, Committee $comite): bool
    {
        $cargo = app(CurrentScope::class)->activeRole();
        $presidido = $comite->depth === 1 ? $comite->id : $comite->parent_id;

        return $cargo?->user_id === $quien->id && $cargo->role === CommitteeUser::PRESIDENT && $cargo->committee_id === $presidido;
    }
}
