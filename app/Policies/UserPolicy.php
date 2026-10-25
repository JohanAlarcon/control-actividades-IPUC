<?php

namespace App\Policies;

use App\Models\User;

/**
 * Las cuentas son asunto del pastor y de nadie mas (RF-003, RF-005, RF-006).
 */
class UserPolicy
{
    public function viewAny(User $quien): bool
    {
        return $quien->is_pastor;
    }

    public function create(User $quien): bool
    {
        return $quien->is_pastor;
    }

    public function update(User $quien, User $cuenta): bool
    {
        return $quien->is_pastor && $quien->site_id === $cuenta->site_id;
    }

    public function resetPassword(User $quien, User $cuenta): bool
    {
        return $this->update($quien, $cuenta);
    }

    /** El pastor no se retira a si mismo: la sede se quedaria sin quien administre. */
    public function revoke(User $quien, User $cuenta): bool
    {
        return $this->update($quien, $cuenta) && ! $cuenta->is_pastor;
    }
}
