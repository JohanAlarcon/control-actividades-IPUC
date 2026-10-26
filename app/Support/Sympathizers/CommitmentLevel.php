<?php

namespace App\Support\Sympathizers;

use App\Models\Sympathizer;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * El nivel de compromiso de un simpatizante (RF-043, R-07, D-10). Se calcula con dos señales —los
 * contactos recientes y los dias desde el ultimo— y nadie lo escribe a mano. Los umbrales viven
 * aqui y en ningun otro lugar: son valores de partida sin confirmar por el cliente (A-01), y se
 * ajustan con una linea cuando el Comite de Evangelismo diga que considera «alejandose».
 */
class CommitmentLevel
{
    /** Cuantos dias atras cuenta un contacto como reciente. */
    public const RECENT_DAYS = 60;

    public const GROWING_MIN_CONTACTS = 3;

    public const GROWING_MAX_DAYS = 15;

    /** Desde cuantos dias sin contacto se considera que la persona se esta alejando. */
    public const DRIFTING_DAYS = 45;

    public const GROWING = 'growing';

    public const STABLE = 'stable';

    public const DRIFTING = 'drifting';

    /** @return array{level: string, recentContacts: int, daysSinceContact: int} */
    public static function of(int $contactosRecientes, CarbonInterface $ultimoContacto, ?CarbonInterface $hoy = null): array
    {
        $dias = (int) $ultimoContacto->copy()->startOfDay()->diffInDays(self::today($hoy));

        $nivel = match (true) {
            $contactosRecientes >= self::GROWING_MIN_CONTACTS && $dias < self::GROWING_MAX_DAYS => self::GROWING,
            $contactosRecientes >= 1 && $dias < self::DRIFTING_DAYS => self::STABLE,
            default => self::DRIFTING,
        };

        return ['level' => $nivel, 'recentContacts' => $contactosRecientes, 'daysSinceContact' => $dias];
    }

    /**
     * El de un simpatizante traido con withCommitment(). El primer contacto tambien es un contacto:
     * quien acaba de llegar no aparece «alejandose».
     */
    public static function forSympathizer(Sympathizer $persona, ?CarbonInterface $hoy = null): array
    {
        $ultimaVisita = $persona->visits_max_visited_at ? Carbon::parse($persona->visits_max_visited_at) : null;
        $ultimo = $ultimaVisita && $ultimaVisita->gt($persona->first_contact_at) ? $ultimaVisita : $persona->first_contact_at;
        $primeroReciente = $persona->first_contact_at->gte(self::recentSince($hoy)) ? 1 : 0;

        return self::of((int) $persona->recent_visits_count + $primeroReciente, $ultimo, $hoy);
    }

    public static function recentSince(?CarbonInterface $hoy = null): CarbonInterface
    {
        return self::today($hoy)->subDays(self::RECENT_DAYS);
    }

    private static function today(?CarbonInterface $hoy): CarbonInterface
    {
        return ($hoy ?? Carbon::now('America/Bogota'))->copy()->startOfDay();
    }
}
