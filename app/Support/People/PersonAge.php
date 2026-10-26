<?php

namespace App\Support\People;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * La edad de una persona, calculada el dia de la consulta (RF-031, D-09). De la fecha de
 * nacimiento cuando existe; si no, de la edad anotada mas los años que pasaron desde que se
 * anoto, para que ningun reporte muestre la edad del año pasado. Sin ninguna de las dos, no hay
 * edad: nunca se reporta cero.
 */
class PersonAge
{
    public const ZONE = 'America/Bogota';

    public static function of(?CarbonInterface $nacimiento, ?int $anotada, ?CarbonInterface $anotadaEl, ?CarbonInterface $hoy = null): ?int
    {
        $hoy = ($hoy ?? Carbon::now(self::ZONE))->copy()->startOfDay();

        if ($nacimiento !== null) {
            return (int) floor($nacimiento->copy()->startOfDay()->diffInYears($hoy));
        }

        if ($anotada !== null && $anotadaEl !== null) {
            return $anotada + (int) floor($anotadaEl->copy()->startOfDay()->diffInYears($hoy));
        }

        return null;
    }
}
