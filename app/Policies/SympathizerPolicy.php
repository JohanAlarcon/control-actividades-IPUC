<?php

namespace App\Policies;

use App\Models\CommitteeUser;
use App\Models\Sympathizer;
use App\Models\User;
use App\Support\Scope\CurrentScope;

/**
 * La policy mas estrecha del proyecto (RF-040, R-06). Registrar, lo puede cualquier cargo. La
 * ficha de seguimiento, solo el pastor y quien preside el comite que acompaña a los simpatizantes:
 * ni los lideres de sus sub-comites, ni su secretario ni su tesorero, ni quien registro a la persona.
 */
class SympathizerPolicy
{
    public function create(User $quien): bool
    {
        $scope = app(CurrentScope::class);

        return $scope->user()?->is($quien) === true && ($scope->isPastor() || $scope->activeRole() !== null);
    }

    public function follow(User $quien, ?Sympathizer $persona = null): bool
    {
        $scope = app(CurrentScope::class);

        if ($scope->user()?->is($quien) !== true) {
            return false;
        }

        if ($scope->isPastor()) {
            return true;
        }

        $cargo = $scope->activeRole();

        return $cargo?->role === CommitteeUser::PRESIDENT && $cargo->committee?->follows_sympathizers === true;
    }

    /**
     * El paso a miembro lo propone el presidente de ese comite, cuando la persona recorrio las
     * cinco etapas; lo aprueba el pastor (RF-019, RN-06).
     */
    public function promote(User $quien, Sympathizer $persona): bool
    {
        return $this->follow($quien, $persona)
            && ! app(CurrentScope::class)->isPastor()
            && $persona->stage === Sympathizer::LAST_STAGE
            && $persona->converted_member_id === null;
    }
}
