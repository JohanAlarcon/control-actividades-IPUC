<?php

namespace App\Support\Activities;

use App\Models\Activity;
use Carbon\CarbonInterface;

/**
 * El aviso de cruce (RF-028, CL-08, CE-04): otras actividades de la congregacion el mismo dia, de
 * cualquier comite. Mira el cronograma compartido, asi que solo ve los siete datos: nunca el
 * presupuesto ni las tareas de otro comite. Es un aviso, no un bloqueo: insistir es de las personas.
 */
class ScheduleConflict
{
    /** @return array<int, array{title: string, committee: string, at: CarbonInterface, location: ?string, sameSlot: bool}> */
    public static function on(CarbonInterface $cuando, ?string $lugar): array
    {
        $dia = $cuando->copy()->setTimezone(ScheduleCards::ZONE);

        return Activity::forSharedCalendar()
            ->whereBetween('activities.scheduled_at', [
                $dia->copy()->startOfDay()->setTimezone(config('app.timezone')),
                $dia->copy()->endOfDay()->setTimezone(config('app.timezone')),
            ])
            ->where('activities.status', '!=', Activity::REJECTED)
            ->orderBy('activities.scheduled_at')
            ->get()
            ->map(fn (Activity $otra) => [
                'title' => $otra->title,
                'committee' => $otra->committee_name,
                'at' => $otra->scheduled_at,
                'location' => $otra->location,
                'sameSlot' => $otra->scheduled_at->format('H:i') === $cuando->format('H:i') && self::mismoLugar($otra->location, $lugar),
            ])
            ->all();
    }

    /** La advertencia en palabras, para quien propone: que actividad es, de que comite, a que hora y donde. */
    public static function describe(array $choques): string
    {
        $partes = array_map(function (array $choque) {
            $hora = $choque['at']->copy()->setTimezone(ScheduleCards::ZONE)->locale('es')->isoFormat('h:mm a');
            $donde = filled($choque['location']) ? " en {$choque['location']}" : '';
            $exacto = $choque['sameSlot'] ? 'a la misma hora y en el mismo lugar, ' : '';

            return "{$exacto}«{$choque['title']}» de {$choque['committee']}, a las {$hora}{$donde}";
        }, $choques);

        $cuantas = count($choques) === 1 ? 'otra actividad' : 'otras actividades';

        return "Ese día ya hay {$cuantas}: ".implode('; ', $partes).'. Coordínenlo antes de insistir.';
    }

    private static function mismoLugar(?string $uno, ?string $otro): bool
    {
        return filled($uno) && filled($otro) && mb_strtolower(trim($uno)) === mb_strtolower(trim($otro));
    }
}
