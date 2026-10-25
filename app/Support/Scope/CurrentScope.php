<?php

namespace App\Support\Scope;

use App\Models\CommitteeUser;
use App\Models\User;
use App\Support\Committees\SubtreeResolver;

/**
 * Quien soy y que puedo ver, resuelto una sola vez por peticion.
 *
 * Regla del principio I: una peticion tiene UN cargo activo, nunca la union de
 * dos. El ambito de ese cargo es su nodo y todos sus descendientes.
 */
class CurrentScope
{
    private ?User $user = null;

    private ?CommitteeUser $activeRole = null;

    /** @var array<int>|null */
    private ?array $committeeIds = null;

    private bool $mustChoose = false;

    public function __construct(private readonly SubtreeResolver $subtree) {}

    public function resolve(?User $user, ?int $activeRoleId = null): void
    {
        $this->reset();

        if (! $user || ! $user->active) {
            return;
        }

        $this->user = $user;

        if ($user->is_pastor) {
            $this->committeeIds = $this->subtree->idsForSite($user->site_id);

            return;
        }

        $cargos = $user->currentRoles()->get();

        if ($cargos->isEmpty()) {
            $this->committeeIds = [];

            return;
        }

        $this->activeRole = $this->pickActiveRole($cargos, $activeRoleId);

        if (! $this->activeRole) {
            $this->mustChoose = true;
            $this->committeeIds = [];

            return;
        }

        $this->committeeIds = $this->subtree->idsFor($this->activeRole->committee_id);
    }

    public function isActive(): bool
    {
        return $this->user !== null;
    }

    public function isPastor(): bool
    {
        return (bool) $this->user?->is_pastor;
    }

    public function user(): ?User
    {
        return $this->user;
    }

    public function activeRole(): ?CommitteeUser
    {
        return $this->activeRole;
    }

    public function siteId(): ?int
    {
        return $this->user?->site_id;
    }

    /** Con dos o mas cargos y ninguno escogido, no se muestra informacion. */
    public function mustChoose(): bool
    {
        return $this->mustChoose;
    }

    public function canSwitch(): bool
    {
        return ! $this->isPastor() && ($this->user?->currentRoles()->count() ?? 0) > 1;
    }

    /** @return array<int> */
    public function committeeIds(): array
    {
        return $this->committeeIds ?? [];
    }

    public function sees(int $committeeId): bool
    {
        return in_array($committeeId, $this->committeeIds(), true);
    }

    private function pickActiveRole($cargos, ?int $activeRoleId): ?CommitteeUser
    {
        if ($activeRoleId !== null) {
            return $cargos->firstWhere('id', $activeRoleId);
        }

        return $cargos->count() === 1 ? $cargos->first() : null;
    }

    private function reset(): void
    {
        $this->user = null;
        $this->activeRole = null;
        $this->committeeIds = null;
        $this->mustChoose = false;
    }
}
