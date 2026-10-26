<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\CommitteeUser;
use App\Models\User;
use App\Support\Scope\CurrentScope;

class ActivityPolicy
{
    /** El cronograma es de toda la congregacion: cualquiera ve los siete datos compartidos (RF-010). */
    public function view(User $quien, Activity $actividad): bool
    {
        return true;
    }

    /** Proponen el lider y el presidente, con el cargo con el que estan actuando (HU-05). */
    public function create(User $quien): bool
    {
        $scope = app(CurrentScope::class);

        return $scope->user()?->is($quien) === true
            && in_array($scope->activeRole()?->role, [CommitteeUser::PRESIDENT, CommitteeUser::LEADER], true);
    }

    /**
     * Solo el comite duenio, o uno que lo contiene, con un cargo activo. Ver una actividad ajena en
     * el cronograma no da permiso de tocarla (data-model §4).
     */
    public function update(User $quien, Activity $actividad): bool
    {
        $scope = app(CurrentScope::class);

        return $scope->user()?->is($quien) === true
            && $scope->activeRole() !== null
            && $scope->sees($actividad->committee_id);
    }
}
