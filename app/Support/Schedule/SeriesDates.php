<?php

namespace App\Support\Schedule;

use Carbon\CarbonImmutable;

/**
 * Las fechas de una actividad que se repite (D-38, RF-088): cada semana, cada quince días o cada
 * mes, de la primera fecha a la última, las dos incluidas. Cada mes cae el mismo día del mes; si
 * ese mes no lo tiene —un 31 en abril—, cae el último día. Se cuenta siempre desde la primera
 * fecha, así que después de un febrero corto vuelve al 31.
 */
class SeriesDates
{
    public const MAX = 60;

    /**
     * Se detiene al pasar del tope: con una fecha de más ya se sabe que no se puede.
     *
     * @return array<int, CarbonImmutable>
     */
    public static function between(string $desde, string $hasta, string $frecuencia): array
    {
        $primera = CarbonImmutable::parse($desde);
        $ultima = CarbonImmutable::parse($hasta);
        $fechas = [];

        for ($vez = 0; count($fechas) <= self::MAX; $vez++) {
            $fecha = match ($frecuencia) {
                'weekly' => $primera->addWeeks($vez),
                'biweekly' => $primera->addWeeks(2 * $vez),
                'monthly' => $primera->addMonthsNoOverflow($vez),
            };

            if ($fecha->greaterThan($ultima)) {
                break;
            }

            $fechas[] = $fecha;
        }

        return $fechas;
    }
}
