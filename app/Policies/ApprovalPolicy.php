<?php

namespace App\Policies;

use App\Models\Approval;
use App\Models\CommitteeUser;
use App\Models\User;
use App\Support\Scope\CurrentScope;

class ApprovalPolicy
{
    /**
     * La bandeja la abre cualquiera con cuenta: quien decide ve lo suyo por resolver, y los
     * demas ven en que van las solicitudes de su comite.
     */
    public function viewAny(User $quien): bool
    {
        return true;
    }

    /**
     * La de nivel presidente la resuelve el presidente del comite de donde viene, o de uno que
     * lo contiene; la de nivel pastor, el pastor (RF-016, RF-017). Nadie decide a nombre de
     * otro: si el cargo esta vacante, lo pendiente pasa al reemplazo (RF-023).
     */
    public function resolve(User $quien, Approval $solicitud): bool
    {
        $scope = app(CurrentScope::class);

        if ($scope->user()?->is($quien) !== true) {
            return false;
        }

        $leToca = $solicitud->level === Approval::LEVEL_PASTOR
            ? $scope->isPastor()
            : $scope->activeRole()?->role === CommitteeUser::PRESIDENT;

        return $leToca && $scope->sees($solicitud->committee_id);
    }
}
