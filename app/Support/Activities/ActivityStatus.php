<?php

namespace App\Support\Activities;

use App\Models\Activity;
use Carbon\CarbonInterface;

/**
 * Los estados de una actividad y cuando pasa de uno a otro, en un solo lugar (R-04, data-model §4,
 * V-05). Sin transiciones hacia atras: una actividad cumplida no vuelve a «en curso».
 */
class ActivityStatus
{
    /**
     * El estado que se muestra hoy. Una aprobada cuya fecha ya llego se ve «en curso» aunque nadie
     * haya marcado una tarea: llegar la fecha es lo primero que ocurrio.
     */
    public static function effective(string $estado, CarbonInterface $fecha, ?CarbonInterface $ahora = null): string
    {
        return $estado === Activity::APPROVED && $fecha->lte($ahora ?? now()) ? Activity::IN_PROGRESS : $estado;
    }

    /**
     * El estado que corresponde guardar despues de un cambio en las tareas: la primera cumplida la
     * pone en curso, y la ultima la deja cumplida. Lo que no esta aprobado no se mueve.
     */
    public static function afterTasks(Activity $actividad, int $tareas): string
    {
        $estado = $actividad->status;

        if ($estado === Activity::APPROVED && $actividad->progress > 0) {
            $estado = Activity::IN_PROGRESS;
        }

        if ($estado === Activity::IN_PROGRESS && $tareas > 0 && $actividad->progress >= 100) {
            $estado = Activity::COMPLETED;
        }

        return $estado;
    }

    /** Sin tareas, el responsable la marca cumplida. Con tareas, se cumple sola al terminarlas. */
    public static function canMarkCompleted(Activity $actividad, int $tareas): bool
    {
        return $tareas === 0 && in_array($actividad->status, [Activity::APPROVED, Activity::IN_PROGRESS], true);
    }

    /** Las tareas se marcan cuando la actividad ya esta aprobada y todavia no se ha cumplido. */
    public static function acceptsTaskChecks(Activity $actividad): bool
    {
        return in_array($actividad->status, [Activity::APPROVED, Activity::IN_PROGRESS], true);
    }
}
