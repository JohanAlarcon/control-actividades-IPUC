<?php

namespace App\Support\Users;

use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\User;
use App\Support\Approvals\TransferPending;
use Illuminate\Support\Facades\DB;

/**
 * Asignar un cargo que ya tiene titular es un relevo: se cierra el anterior y se
 * abre una fila nueva. Nunca se borra, porque la historia tiene que seguir
 * diciendo quien aprobo que y cuando (RN-09).
 */
class AssignRole
{
    public function __construct(private readonly TransferPending $traspaso) {}

    public function handle(User $persona, Committee $comite, string $rol): CommitteeUser
    {
        return DB::transaction(function () use ($persona, $comite, $rol) {
            $this->cerrarAlTitular($comite, $rol);

            return CommitteeUser::create([
                'site_id' => $comite->site_id,
                'user_id' => $persona->id,
                'committee_id' => $comite->id,
                'role' => $rol,
                'started_at' => now()->toDateString(),
            ]);
        });
    }

    /** Cerrar un cargo sin reemplazo: queda vacante, y sus pendientes quedan marcados. */
    public function vacate(CommitteeUser $cargo): void
    {
        DB::transaction(function () use ($cargo) {
            $cargo->update(['ended_at' => now()->toDateString()]);
            $this->traspaso->fromRole($cargo);
        });
    }

    public function currentHolder(int $committeeId, string $rol): ?CommitteeUser
    {
        return CommitteeUser::query()
            ->where('committee_id', $committeeId)
            ->where('role', $rol)
            ->whereNull('ended_at')
            ->first();
    }

    private function cerrarAlTitular(Committee $comite, string $rol): void
    {
        $titular = $this->currentHolder($comite->id, $rol);

        if ($titular) {
            $this->vacate($titular);
        }
    }
}
