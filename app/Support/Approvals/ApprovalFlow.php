<?php

namespace App\Support\Approvals;

use App\Models\Activity;
use App\Models\Approval;
use App\Models\CommitteeUser;
use App\Models\Sympathizer;
use App\Models\User;
use App\Support\Committees\SubtreeResolver;
use App\Support\Sympathizers\ConvertToMember;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * La cadena de aprobacion, en un solo lugar (data-model §5; RF-016 a RF-019). Solo el pastor
 * aprueba; el presidente revisa: da su visto bueno y la solicitud sube al pastor, o la devuelve
 * con el motivo (D-24). Es lo unico que crea solicitudes y lo unico que decide cuando una sube.
 * Es la regla que mas se ha movido en el proyecto: aqui se cambia, y en ningun otro lugar.
 */
class ApprovalFlow
{
    /**
     * Pide aprobacion. Lo de un presidente le llega directo al pastor; lo de los demas cargos,
     * primero a su presidente, que lo revisa (RF-016, RF-017). Sin excepciones por tipo ni monto.
     */
    public function request(CommitteeUser $cargo, Model $objeto, string $tipo, string $titulo, ?string $detalle = null, ?float $monto = null): Approval
    {
        $esPresidente = $cargo->role === CommitteeUser::PRESIDENT;

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
     * Quien recibe lo que pide ese cargo, para decirselo con nombre propio (RF-016, RF-017): el
     * pastor, si lo pide un presidente; si no, quien hoy preside el comite de primer nivel del que
     * cuelga el suyo. Null si ese cargo esta vacante: la solicitud espera al reemplazo (RF-023).
     */
    public function recipientFor(CommitteeUser $cargo): ?User
    {
        if ($cargo->role === CommitteeUser::PRESIDENT) {
            return User::where('site_id', $cargo->site_id)->where('is_pastor', true)->where('active', true)->first();
        }

        $presidencia = CommitteeUser::current()
            ->where('committee_id', app(SubtreeResolver::class)->rootIdOf($cargo->committee))
            ->where('role', CommitteeUser::PRESIDENT)
            ->select('user_id');

        return User::where('active', true)->whereIn('id', $presidencia)->first();
    }

    /**
     * El visto bueno del presidente o la aprobacion del pastor, segun quien la tenga. El visto
     * bueno nunca cierra: queda constancia de que paso por el presidente y la solicitud sube al
     * pastor (HU-06). Solo el pastor la deja aprobada, y entonces se aplica su efecto (D-24).
     */
    public function approve(Approval $solicitud, User $quien): Approval
    {
        return DB::transaction(function () use ($solicitud, $quien) {
            $solicitud = $this->tomar($solicitud);

            if ($solicitud->level === Approval::LEVEL_PRESIDENT) {
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

    /**
     * Varias de la misma clase en una sola accion, con una sola confirmacion (RF-022). Todas o
     * ninguna: si alguna ya la resolvio otra persona, no se aprueba ninguna y se explica. Cada una
     * queda con su propio registro, como si se hubiera aprobado sola.
     */
    public function approveMany(Collection $solicitudes, User $quien): void
    {
        if ($solicitudes->pluck('type')->unique()->count() > 1) {
            throw ValidationException::withMessages(['approval' => 'Solo se aprueban juntas las solicitudes de la misma clase.']);
        }

        DB::transaction(fn () => $solicitudes->each(fn (Approval $solicitud) => $this->approve($solicitud, $quien)));
    }

    /**
     * Rechaza, con el motivo que va a leer quien la pidio (RF-020), y siempre cierra. Si la
     * rechaza el presidente, la solicitud vuelve a quien la pidio y no llega al pastor (D-24).
     */
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
     * Lo que cambia en el objeto al cerrarse. La actividad queda aprobada en el cronograma con
     * nombre y fecha, o rechazada con su motivo; el simpatizante aprobado entra a la lista de
     * miembros (RF-045). El gasto, la tarea y el cambio de fecha aplican su efecto cuando existan
     * sus modulos.
     */
    private function aplicarEfecto(Approval $solicitud): void
    {
        $objeto = $solicitud->approvable;

        if ($solicitud->type === Approval::TYPE_NEW_MEMBER && $objeto instanceof Sympathizer && $solicitud->status === Approval::APPROVED) {
            ConvertToMember::handle($objeto);

            return;
        }

        if ($solicitud->type !== Approval::TYPE_ACTIVITY || ! $objeto instanceof Activity) {
            return;
        }

        $objeto->update($solicitud->status === Approval::APPROVED
            ? ['status' => Activity::APPROVED, 'approved_by_user_id' => $solicitud->resolved_by_user_id, 'approved_at' => $solicitud->resolved_at]
            : ['status' => Activity::REJECTED, 'rejection_reason' => $solicitud->reason]);
    }
}
