<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\Approval;
use App\Models\CommitteeUser;
use App\Models\User;
use App\Support\Approvals\ApprovalFlow;
use Illuminate\Database\Seeder;

/**
 * Solicitudes de demostracion, para ver la bandeja en cada nivel: lo que espera al presidente, lo
 * que ya subio al pastor y una que no se aprobo. TODO ES FICTICIO. Pasan por ApprovalFlow, como
 * pasarian de verdad. Necesita las cuentas de DemoSeeder.
 */
class DemoApprovalsSeeder extends Seeder
{
    public function run(ApprovalFlow $flujo): void
    {
        $presidente = User::where('username', 'presidente.alabanza')->firstOrFail();

        $this->pedir('lider.sonido', Approval::TYPE_ACTIVITY, 'Prueba de sonido para la convención', 'Evento', diasAtras: 3);
        $this->pedir('lider.sonido', Approval::TYPE_EXPENSE, 'Cables y extensiones para el montaje', 'Evento', diasAtras: 6, monto: 850_000);
        $this->pedir('presidente.alabanza', Approval::TYPE_ACTIVITY, 'Culto de alabanza unido', 'Culto', diasAtras: 2);

        $flujo->approve($this->pedir('lider.coro', Approval::TYPE_ACTIVITY, 'Ensayo general del coro para la Santa Cena', 'Ensayo', diasAtras: 5), $presidente);

        $flujo->reject(
            $this->pedir('lider.coro', Approval::TYPE_DATE_CHANGE, 'Pasar el ensayo del coro al domingo', 'Ensayo', diasAtras: 8),
            $presidente,
            'Ese domingo es la Santa Cena y el templo está ocupado. Mejor el sábado siguiente.',
        );
    }

    private function pedir(string $usuario, string $tipo, string $titulo, string $clase, int $diasAtras, ?float $monto = null): Approval
    {
        $cargo = CommitteeUser::whereRelation('user', 'username', $usuario)->firstOrFail();

        $actividad = Activity::create([
            'site_id' => $cargo->site_id,
            'committee_id' => $cargo->committee_id,
            'title' => $titulo,
            'scheduled_at' => now()->addDays(10 + $diasAtras)->setTime(19, 0),
            'location' => 'Templo central',
            'activity_type_id' => ActivityType::where('site_id', $cargo->site_id)->where('name', $clase)->value('id'),
            'status' => Activity::PENDING,
            'proposed_by_user_id' => $cargo->user_id,
            'proposed_at' => now()->subDays($diasAtras),
        ]);

        $solicitud = app(ApprovalFlow::class)->request($cargo, $actividad, $tipo, $titulo, null, $monto);
        $solicitud->update(['requested_at' => now()->subDays($diasAtras)]);

        return $solicitud;
    }
}
