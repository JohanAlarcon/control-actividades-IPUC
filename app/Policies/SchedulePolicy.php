<?php

namespace App\Policies;

use App\Models\CommitteeUser;
use App\Models\Schedule;
use App\Models\User;
use App\Support\Scope\CurrentScope;

/**
 * Los cronogramas internos (RF-086). Los ve todo cargo del comité dueño, sus ascendientes y el
 * pastor; los crea y les agrega actividades el presidente o el líder, sobre su propio nodo.
 */
class SchedulePolicy
{
    /** La tercera capa del filtro: aunque la ruta ya dejó fuera lo ajeno, se vuelve a preguntar. */
    public function view(User $quien, Schedule $cronograma): bool
    {
        $scope = app(CurrentScope::class);

        return $scope->user()?->is($quien) === true && $scope->sees((int) $cronograma->committee_id);
    }

    public function create(User $quien): bool
    {
        return $this->dirige($quien) !== null;
    }

    public function addActivity(User $quien, Schedule $cronograma): bool
    {
        return $cronograma->kind === Schedule::INTERNAL && $this->dirige($quien)?->committee_id === $cronograma->committee_id;
    }

    /** El cargo activo, si es el presidente o el líder de su nodo. */
    private function dirige(User $quien): ?CommitteeUser
    {
        $cargo = app(CurrentScope::class)->activeRole();

        return $cargo?->user_id === $quien->id && in_array($cargo->role, [CommitteeUser::PRESIDENT, CommitteeUser::LEADER], true) ? $cargo : null;
    }
}
