<?php

namespace App\Policies;

use App\Models\Committee;
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
}
