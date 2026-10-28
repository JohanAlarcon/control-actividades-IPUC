<?php

namespace App\Support\People;

use App\Models\Member;
use App\Models\Sympathizer;
use App\Support\Activities\ScheduleCards;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Los cumpleaños se calculan, no se guardan (D-42, RF-095): salen de la fecha de nacimiento de los
 * miembros activos y de los simpatizantes del ámbito, con el filtro puesto. Los niños de la Escuela
 * Dominical se suman cuando exista su ficha (fase R4).
 */
class Birthdays
{
    /** La alerta del inicio avisa de hoy y de los siete días que siguen. */
    public const DAYS_AHEAD = 7;

    /** @return Collection<int, array{kind: string, id: int, name: string, code: ?string, birthDate: ?Carbon}> */
    public static function people(): Collection
    {
        $miembros = Member::query()
            ->where('members.active', true)
            ->get(['members.id', 'members.name', 'members.code', 'members.birth_date'])
            ->map(fn (Member $miembro) => ['kind' => 'member', 'id' => $miembro->id, 'name' => $miembro->name, 'code' => $miembro->code, 'birthDate' => $miembro->birth_date]);

        // Quien ya es miembro sale como miembro, no dos veces.
        $simpatizantes = Sympathizer::query()
            ->whereNull('sympathizers.converted_member_id')
            ->get(['sympathizers.id', 'sympathizers.name', 'sympathizers.birth_date'])
            ->map(fn (Sympathizer $simpatizante) => ['kind' => 'sympathizer', 'id' => $simpatizante->id, 'name' => $simpatizante->name, 'code' => null, 'birthDate' => $simpatizante->birth_date]);

        return $miembros->concat($simpatizantes);
    }

    /** Quien nació un 29 de febrero, en los años que no son bisiestos cumple el 28 (CL-32). */
    public static function dayIn(Carbon $nacimiento, int $anio): Carbon
    {
        $dia = $nacimiento->month === 2 && $nacimiento->day === 29 && ! Carbon::create($anio)->isLeapYear() ? 28 : $nacimiento->day;

        return Carbon::create($anio, $nacimiento->month, $dia, 0, 0, 0, ScheduleCards::ZONE);
    }

    /** Los que cumplen hoy y en los próximos siete días, en orden, con los años que cumplen. */
    public static function soon(Collection $personas, Carbon $hoy): array
    {
        $hoy = $hoy->copy()->startOfDay();
        $hasta = $hoy->copy()->addDays(self::DAYS_AHEAD)->toDateString();

        return self::cards($personas->filter(fn (array $persona) => $persona['birthDate'] !== null), function (Carbon $nacimiento) use ($hoy) {
            $dia = self::dayIn($nacimiento, $hoy->year);

            return $dia->lt($hoy) ? self::dayIn($nacimiento, $hoy->year + 1) : $dia;
        })->filter(fn (array $tarjeta) => $tarjeta['date'] <= $hasta)->values()->all();
    }

    /** Los de un mes, por día, con los años que cumplen ese año. */
    public static function ofMonth(Collection $personas, int $mes, int $anio): array
    {
        return self::cards($personas->filter(fn (array $persona) => $persona['birthDate']?->month === $mes), fn (Carbon $nacimiento) => self::dayIn($nacimiento, $anio))
            ->values()
            ->all();
    }

    private static function cards(Collection $personas, callable $cumpleanos): Collection
    {
        return $personas
            ->map(function (array $persona) use ($cumpleanos) {
                $dia = $cumpleanos($persona['birthDate']);

                return ['kind' => $persona['kind'], 'id' => $persona['id'], 'name' => $persona['name'], 'code' => $persona['code'], 'date' => $dia->toDateString(), 'turns' => $dia->year - $persona['birthDate']->year];
            })
            // Una fecha de nacimiento futura es un error de digitación, no un cumpleaños.
            ->filter(fn (array $tarjeta) => $tarjeta['turns'] > 0)
            ->sortBy([['date', 'asc'], ['name', 'asc']]);
    }
}
