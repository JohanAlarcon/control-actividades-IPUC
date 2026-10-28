<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\Approval;
use App\Models\CommitteeUser;
use App\Models\Schedule;
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
        $conquistadores = User::where('username', 'presidente.conquistadores')->firstOrFail();

        $this->pedir('lider.hijas', Approval::TYPE_ACTIVITY, 'Taller de manualidades para la Navidad', 'Reunión', diasAtras: 3);
        $this->pedir('lider.hijas', Approval::TYPE_EXPENSE, 'Materiales para el taller de manualidades', 'Reunión', diasAtras: 6, monto: 850_000);
        $this->pedir('presidente.alabanza', Approval::TYPE_ACTIVITY, 'Culto de alabanza unido', 'Culto', diasAtras: 2);

        $flujo->approve($this->pedir('lider.bethel', Approval::TYPE_ACTIVITY, 'Ensayo de la obra de teatro de Bethel', 'Ensayo', diasAtras: 5), $conquistadores);

        $flujo->reject(
            $this->pedir('lider.bethel', Approval::TYPE_DATE_CHANGE, 'Pasar el ensayo de Bethel al domingo', 'Ensayo', diasAtras: 8),
            $conquistadores,
            'Ese domingo es la Santa Cena y el templo está ocupado. Mejor el sábado siguiente.',
        );
    }

    private function pedir(string $usuario, string $tipo, string $titulo, string $clase, int $diasAtras, ?float $monto = null): Approval
    {
        $cargo = CommitteeUser::whereRelation('user', 'username', $usuario)->firstOrFail();

        $actividad = Activity::create([
            'site_id' => $cargo->site_id,
            'committee_id' => $cargo->committee_id,
            'schedule_id' => Schedule::generalOf($cargo->site_id, now()->addDays(10 + $diasAtras)->year)->id,
            'title' => $titulo,
            'scheduled_at' => now()->addDays(10 + $diasAtras)->setTime(16, 0),
            'ends_at' => now()->addDays(10 + $diasAtras)->setTime(18, 0),
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
