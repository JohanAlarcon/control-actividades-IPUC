<?php

namespace App\Support\Approvals;

use App\Models\Approval;
use App\Models\CommitteeUser;
use App\Support\Committees\SubtreeResolver;

/**
 * Un cargo vacante no puede dejar solicitudes sin responsable (RF-023, CL-06).
 *
 * Quien responde por una solicitud no es una columna: es quien tenga hoy el
 * cargo en ese comite. Por eso el relevo no reasigna nada, sino que deja
 * constancia de donde venia cada pendiente y cuando cambio de manos.
 */
class TransferPending
{
    public function __construct(private readonly SubtreeResolver $subtree) {}

    /** @return int cuantas solicitudes cambiaron de manos */
    public function fromRole(CommitteeUser $saliente): int
    {
        if ($saliente->role !== CommitteeUser::PRESIDENT) {
            return 0;
        }

        // La actualizacion masiva no dispara los eventos del modelo, asi que no
        // pasa por la guarda de inmutabilidad. Filtrar por pendientes es lo que
        // garantiza que no se toque ninguna decision ya tomada.
        return Approval::query()
            ->whereIn('committee_id', $this->subtree->idsFor($saliente->committee_id))
            ->where('level', Approval::LEVEL_PRESIDENT)
            ->where('status', Approval::PENDING)
            ->update([
                'transferred_from_user_id' => $saliente->user_id,
                'transferred_at' => now(),
            ]);
    }
}
