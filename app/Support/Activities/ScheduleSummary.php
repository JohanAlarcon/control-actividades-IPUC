<?php

namespace App\Support\Activities;

use App\Models\Activity;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Los numeros del cronograma, siempre sobre lo que se esta viendo (RF-030). El cumplimiento mide
 * solo lo que ya debia hacerse: de las aprobadas cuya fecha llego, y de las ya cumplidas, cuantas
 * se cumplieron. Lo que espera aprobacion no cuenta, y lo que todavia no llega tampoco.
 */
class ScheduleSummary
{
    public static function of(Collection $tarjetas, ?CarbonInterface $ahora = null): array
    {
        $ahora ??= now();
        $porEstado = $tarjetas->countBy('status');

        $debidas = $tarjetas->filter(fn (array $tarjeta) => $tarjeta['status'] === Activity::COMPLETED
            || ($tarjeta['status'] !== Activity::PENDING && Carbon::parse($tarjeta['scheduledAt'])->lte($ahora)));
        $cumplidas = $debidas->where('status', Activity::COMPLETED)->count();

        return [
            'total' => $tarjetas->count(),
            'completed' => $porEstado[Activity::COMPLETED] ?? 0,
            'inProgress' => $porEstado[Activity::IN_PROGRESS] ?? 0,
            'approved' => $porEstado[Activity::APPROVED] ?? 0,
            'pending' => $porEstado[Activity::PENDING] ?? 0,
            'due' => $debidas->count(),
            'compliance' => $debidas->isEmpty() ? null : (int) round($cumplidas / $debidas->count() * 100),
        ];
    }
}
