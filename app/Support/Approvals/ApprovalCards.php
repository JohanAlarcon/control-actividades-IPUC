<?php

namespace App\Support\Approvals;

use App\Models\Activity;
use App\Models\Approval;
use App\Models\CommitteeUser;
use App\Support\Scope\CurrentScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;

/**
 * Las solicitudes como las ve la gente: con los nombres ya puestos. Las usan la bandeja y la
 * pantalla de inicio, y por eso viven aqui y no en un controlador.
 */
class ApprovalCards
{
    /**
     * Las solicitudes que el cargo ve, con los nombres ya puestos: una consulta por lista, tenga
     * las que tenga. El filtro de alcance va sobre approvals; la actividad solo se cruza si es del
     * mismo comite de la solicitud, para que el cruce no traiga nada de afuera.
     */
    public static function query(): Builder
    {
        return Approval::query()
            ->join('committees', 'committees.id', '=', 'approvals.committee_id')
            ->join('users as requester', 'requester.id', '=', 'approvals.requested_by_user_id')
            ->leftJoin('users as escalator', 'escalator.id', '=', 'approvals.escalated_by_user_id')
            ->leftJoin('users as resolver', 'resolver.id', '=', 'approvals.resolved_by_user_id')
            ->leftJoin('activities', fn (JoinClause $cruce) => $cruce
                ->on('activities.id', '=', 'approvals.approvable_id')
                ->on('activities.committee_id', '=', 'approvals.committee_id')
                ->where('approvals.approvable_type', (new Activity)->getMorphClass()))
            ->select([
                'approvals.*',
                'committees.name as committee_name',
                'requester.name as requested_by_name',
                'escalator.name as escalated_by_name',
                'resolver.name as resolved_by_name',
                'activities.scheduled_at as activity_date',
                'activities.location as activity_location',
            ])
            ->withCasts(['activity_date' => 'datetime']);
    }

    /**
     * Lo que la tarjeta dice sin abrir nada (T068): de donde viene, quien la pidio, por quien ya
     * paso, cuanto cuesta, que mueve en el cronograma y desde cuando espera.
     */
    public static function card(Approval $solicitud): array
    {
        return [
            'id' => $solicitud->id,
            'type' => $solicitud->type,
            'title' => $solicitud->title,
            'detail' => $solicitud->detail,
            'amount' => $solicitud->amount === null ? null : (float) $solicitud->amount,
            'committee' => $solicitud->committee_name,
            'requestedBy' => $solicitud->requested_by_name,
            'escalatedBy' => $solicitud->escalated_by_name,
            'level' => $solicitud->level,
            'status' => $solicitud->status,
            'waitingSince' => ($solicitud->escalated_at ?? $solicitud->requested_at)->toIso8601String(),
            'resolvedBy' => $solicitud->resolved_by_name,
            'resolvedAt' => $solicitud->resolved_at?->toIso8601String(),
            'reason' => $solicitud->reason,
            'activity' => $solicitud->activity_date
                ? ['date' => $solicitud->activity_date->toIso8601String(), 'location' => $solicitud->activity_location]
                : null,
        ];
    }

    /** El nivel de solicitudes que decide el cargo activo: el pastor aprueba, el presidente revisa (D-24). */
    public static function levelDecidedBy(CurrentScope $scope): ?string
    {
        return match (true) {
            $scope->isPastor() => Approval::LEVEL_PASTOR,
            $scope->activeRole()?->role === CommitteeUser::PRESIDENT => Approval::LEVEL_PRESIDENT,
            default => null,
        };
    }
}
