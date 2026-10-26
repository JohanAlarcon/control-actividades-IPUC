<?php

namespace App\Support\Approvals;

use App\Models\Activity;
use App\Models\Approval;
use App\Models\CommitteeUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * La cadena de escalamiento, en un solo lugar (data-model §5; RF-016 a RF-019; V-04). Es lo
 * unico que crea solicitudes y lo unico que decide si una aprobacion cierra o sube al pastor.
 * Es la regla que mas se ha movido en el proyecto: aqui se cambia, y en ningun otro lugar.
 */
class ApprovalFlow
{
    /**
     * Pide aprobacion. Lo de un presidente sube al pastor; lo de los demas cargos, a su
     * presidente. Devuelve null cuando no hace falta pedir: el gasto del propio comite lo
     * autoriza el presidente, y queda con su nombre en el presupuesto (RF-018).
     */
    public function request(CommitteeUser $cargo, Model $objeto, string $tipo, string $titulo, ?string $detalle = null, ?float $monto = null): ?Approval
    {
        $esPresidente = $cargo->role === CommitteeUser::PRESIDENT;

        if ($esPresidente && $tipo === Approval::TYPE_EXPENSE) {
            return null;
        }

        return Approval::create([
            'site_id' => $cargo->site_id,
            'committee_id' => $cargo->committee_id,
            'approvable_type' => $objeto->getMorphClass(),
            'approvable_id' => $objeto->getKey(),
            'type' => $tipo,
            'level' => $esPresidente ? Approval::LEVEL_PASTOR : Approval::LEVEL_PRESIDENT,
            'status' => Approval::PENDING,
            'title' => $titulo,
            'detail' => $detalle,
            'amount' => $monto,
            'requested_by_user_id' => $cargo->user_id,
            'requested_at' => now(),
        ]);
    }

    /**
     * Aprueba. Una actividad nueva aprobada por el presidente no cierra: queda constancia de que
     * paso por el y sube al pastor (HU-06, escenario 2). Todo lo demas cierra aqui.
     */
    public function approve(Approval $solicitud, User $quien): Approval
    {
        return DB::transaction(function () use ($solicitud, $quien) {
            $solicitud = $this->tomar($solicitud);

            if ($solicitud->level === Approval::LEVEL_PRESIDENT && $solicitud->type === Approval::TYPE_ACTIVITY) {
                $solicitud->update([
                    'level' => Approval::LEVEL_PASTOR,
                    'escalated_by_user_id' => $quien->id,
                    'escalated_at' => now(),
                ]);

                return $solicitud;
            }

            return $this->cerrar($solicitud, ['status' => Approval::APPROVED, 'resolved_by_user_id' => $quien->id]);
        });
    }

    /** Rechaza, con el motivo que va a leer quien la pidio (RF-020). Rechazar siempre cierra. */
    public function reject(Approval $solicitud, User $quien, string $motivo): Approval
    {
        return DB::transaction(fn () => $this->cerrar($this->tomar($solicitud), [
            'status' => Approval::REJECTED,
            'resolved_by_user_id' => $quien->id,
            'reason' => trim($motivo),
        ]));
    }

    /**
     * Vuelve a leer la solicitud, bloqueada hasta terminar, y confirma que nadie la movio desde
     * que la persona la abrio. Asi un doble toque en el boton no produce dos decisiones, ni un
     * presidente cierra lo que ya habia subido al pastor (RF-057).
     */
    private function tomar(Approval $vista): Approval
    {
        $actual = Approval::whereKey($vista->id)->lockForUpdate()->firstOrFail();

        if (! $actual->isPending()) {
            $quien = $actual->resolvedBy?->name ?? 'otra persona';

            throw ValidationException::withMessages([
                'approval' => "Esta solicitud ya la resolvió {$quien}. Una decisión no se cambia: si hay que corregirla, se registra una nueva.",
            ]);
        }

        if ($actual->level !== $vista->level) {
            throw ValidationException::withMessages(['approval' => 'Esta solicitud ya pasó a la revisión del pastor.']);
        }

        return $actual;
    }

    private function cerrar(Approval $solicitud, array $decision): Approval
    {
        $solicitud->update($decision + ['resolved_at' => now()]);
        $this->aplicarEfecto($solicitud);

        return $solicitud;
    }

    /**
     * Lo que cambia en el objeto al cerrarse. Por ahora, la actividad: queda aprobada en el
     * cronograma con nombre y fecha, o rechazada con su motivo. El gasto, la tarea y el cambio
     * de fecha aplican su efecto cuando existan sus modulos.
     */
    private function aplicarEfecto(Approval $solicitud): void
    {
        $actividad = $solicitud->approvable;

        if ($solicitud->type !== Approval::TYPE_ACTIVITY || ! $actividad instanceof Activity) {
            return;
        }

        $actividad->update($solicitud->status === Approval::APPROVED
            ? ['status' => Activity::APPROVED, 'approved_by_user_id' => $solicitud->resolved_by_user_id, 'approved_at' => $solicitud->resolved_at]
            : ['status' => Activity::REJECTED, 'rejection_reason' => $solicitud->reason]);
    }
}
