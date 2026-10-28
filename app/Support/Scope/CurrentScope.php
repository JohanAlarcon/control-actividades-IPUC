<?php

namespace App\Support\Scope;

use App\Models\CommitteeUser;
use App\Models\User;
use App\Support\Committees\SubtreeResolver;
use Illuminate\Support\Collection;

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

    /** Los cargos vigentes se cargan UNA vez con su comite y se reutilizan. */
    private ?Collection $roles = null;

    public function __construct(private readonly SubtreeResolver $subtree) {}

    public function resolve(?User $user, ?int $activeRoleId = null): void
    {
        $this->reset();

        if (! $user || ! $user->active) {
            return;
        }

        if ($user->is_pastor) {
            $this->user = $user;
            $this->committeeIds = $this->subtree->idsForSite($user->site_id);

            return;
        }

        // Todo se consulta ANTES de activar el ambito. Si se activara primero,
        // el filtro se aplicaria a su propia consulta y no traeria ningun comite.
        $cargos = $user->currentRoles()->with('committee')->get();
        $activo = $this->pickActiveRole($cargos, $activeRoleId);
        $ids = $activo ? $this->subtree->idsFor($activo->committee_id, $activo->committee?->depth ?? 1) : [];

        $this->user = $user;
        $this->roles = $cargos;
        $this->activeRole = $activo;
        $this->committeeIds = $ids;
        $this->mustChoose = $cargos->isNotEmpty() && ! $activo;
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

    /**
     * Si el cargo activo es de BIS, el comite que recibe a los simpatizantes (D-34). El comite ya
     * viene cargado con los cargos: preguntarlo no cuesta consultas.
     */
    public function isReception(): bool
    {
        return $this->activeRole()?->committee?->receives_sympathizers === true;
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
        return ! $this->isPastor() && $this->roles()->count() > 1;
    }

    /** Los cargos vigentes ya cargados: no se vuelven a consultar. */
    public function roles(): Collection
    {
        return $this->roles ?? collect();
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
        $this->roles = null;
    }
}
