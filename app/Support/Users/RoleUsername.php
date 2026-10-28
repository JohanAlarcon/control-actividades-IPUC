<?php

namespace App\Support\Users;

use App\Models\CommitteeUser;
use Illuminate\Support\Str;

/**
 * El usuario de la cuenta de un cargo (RF-123): el cargo y el comité, como «presidente.alabanza»
 * o «lider.bethel». Del comité va la palabra que lo distingue, sin «Comité de», tildes ni
 * espacios. Si esa palabra es muy corta —«Obra Social», «Red de Familia»— se le junta la que
 * sigue, y el número del final se conserva: «misiones1».
 */
class RoleUsername
{
    private const ROLES = [
        CommitteeUser::PRESIDENT => 'presidente',
        CommitteeUser::SECRETARY => 'secretario',
        CommitteeUser::TREASURER => 'tesorero',
        CommitteeUser::LEADER => 'lider',
    ];

    private const SOBRAN = ['comite', 'subcomite', 'de', 'del', 'la', 'las', 'los', 'el', 'y', 'su', 'sus'];

    public static function for(string $rol, string $comite): string
    {
        $palabras = collect(preg_split('/\s+/', Str::lower(Str::ascii($comite))))
            ->map(fn (string $palabra) => preg_replace('/[^a-z0-9]/', '', $palabra))
            ->filter()
            ->reject(fn (string $palabra) => in_array($palabra, self::SOBRAN, true))
            ->values();

        $numero = $palabras->first(fn (string $palabra) => ctype_digit($palabra)) ?? '';
        $letras = $palabras->reject(fn (string $palabra) => ctype_digit($palabra))->values();
        $nombre = $letras->first() ?? 'comite';

        if (strlen($nombre) <= 4 && $letras->count() > 1) {
            $nombre .= $letras[1];
        }

        return self::ROLES[$rol].'.'.$nombre.$numero;
    }
}
