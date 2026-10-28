<?php

namespace App\Support\Activities;

use Illuminate\Support\Carbon;

/**
 * La fecha y la hora que escoge la persona se cuentan en hora de Colombia, la configure como la
 * configure el servidor (S-04), y se guardan en la hora de la aplicación.
 */
class LocalTime
{
    public static function of(string $fecha, string $hora): Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i', "{$fecha} {$hora}", ScheduleCards::ZONE)->setTimezone(config('app.timezone'));
    }
}
