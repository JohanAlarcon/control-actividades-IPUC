<?php

namespace App\Support\Activities;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Las actividades como las muestra el cronograma. Las propias van completas; las ajenas, solo con
 * los seis datos que se comparten (RF-010), y nada mas: ni descripcion, ni presupuesto, ni tareas,
 * ni avance, ni el tema ni la marca de color (P-09).
 */
class ScheduleCards
{
    /** El cronograma se cuenta en hora de Colombia, la tenga como la tenga el servidor (S-04). */
    public const ZONE = 'America/Bogota';

    /**
     * Las actividades del cargo, con el filtro de alcance puesto. Trae el nombre del comite, el de
     * su comite padre (para filtrar por un comite con todo lo que cuelga de el) y la clase.
     */
    public static function ownQuery(): Builder
    {
        return Activity::query()
            ->join('committees', 'committees.id', '=', 'activities.committee_id')
            ->leftJoin('committees as parents', 'parents.id', '=', 'committees.parent_id')
            ->leftJoin('activity_types', 'activity_types.id', '=', 'activities.activity_type_id')
            ->select([
                'activities.*',
                'committees.name as committee_name',
                'committees.parent_id as committee_parent_id',
                'parents.name as parent_name',
                'activity_types.name as type_name',
            ]);
    }

    /** @return array{0: Carbon, 1: Carbon} el primero y el ultimo instante del año, en Colombia */
    public static function yearRange(int $anio): array
    {
        $desde = Carbon::create($anio, 1, 1, 0, 0, 0, self::ZONE);

        return [$desde->copy()->setTimezone(config('app.timezone')), $desde->copy()->endOfYear()->setTimezone(config('app.timezone'))];
    }

    /** Los seis datos que se comparten de cualquier actividad, y el numero para abrirla (RF-010, R-11). */
    public static function shared(Activity $actividad): array
    {
        return [
            'id' => $actividad->id,
            'title' => $actividad->title,
            'scheduledAt' => $actividad->scheduled_at->toIso8601String(),
            'endsAt' => $actividad->ends_at->toIso8601String(),
            'location' => $actividad->location,
            'committee' => $actividad->committee_name,
            'status' => ActivityStatus::effective($actividad->status, $actividad->scheduled_at),
        ];
    }

    /** Una actividad del propio comite: lo compartido, mas lo que solo ve su comite. */
    public static function own(Activity $actividad): array
    {
        return self::shared($actividad) + [
            'description' => $actividad->description,
            'theme' => $actividad->theme,
            'highlight' => $actividad->highlight,
            'committeeId' => $actividad->committee_id,
            'type' => $actividad->type_name,
            'progress' => $actividad->progress,
            'budget' => $actividad->budget_amount === null ? null : (float) $actividad->budget_amount,
            'fromPlan' => $actividad->annual_plan_id !== null,
        ];
    }

    /** El mes de una tarjeta, en Colombia. */
    public static function monthOf(array $tarjeta): int
    {
        return Carbon::parse($tarjeta['scheduledAt'])->setTimezone(self::ZONE)->month;
    }

    /** Los doce meses con sus actividades; o solo uno, si se filtro por mes. */
    public static function byMonth(Collection $tarjetas, ?int $soloMes = null): array
    {
        return collect($soloMes ? [$soloMes] : range(1, 12))
            ->map(fn (int $mes) => [
                'month' => $mes,
                'activities' => $tarjetas->filter(fn (array $tarjeta) => self::monthOf($tarjeta) === $mes)->values()->all(),
            ])
            ->all();
    }
}
