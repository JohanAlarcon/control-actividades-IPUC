<?php

namespace App\Support\Committees;

use App\Models\Committee;
use App\Models\Site;
use Illuminate\Support\Str;

/**
 * La estructura de comités de la sede, como quedó en el levantamiento del 2026-10-26: quince
 * comités y seis sub-comités. La usan la preparación de la sede en el servidor (RF-123) y la
 * demostración, para que las dos digan lo mismo. Son nombres de comités, no de personas.
 */
class SiteStructure
{
    public const COMMITTEES = [
        'Comité de Damas Dorcas' => ['Sub-comité Hijas del Rey', 'Sub-comité Mensajeros de su Gracia', 'Sub-comité Mujeres Virtuosas'],
        'Comité de Conquistadores Pentecostales' => ['Sub-comité Bethel', 'Sub-comité Emmanuel', 'Sub-comité Linaje'],
        'Comité de Misiones y Evangelismo 1' => [],
        'Comité de Misiones y Evangelismo 2' => [],
        'Comité de Misiones y Evangelismo 3' => [],
        'Comité de Obra Social' => [],
        'Comité de Alabanza' => [],
        'Comité Decom' => [],
        'Comité BIS' => [],
        'Comité de Vigilancia' => [],
        'Comité de Sonidos' => [],
        'Comité de Red de Familia' => [],
        'Comité de Crecimiento Espiritual' => [],
        'Comité de Escuela Dominical' => [],
        'Comité de Caballeros' => [],
    ];

    /** El que recibe a los simpatizantes de la sede y los asigna (D-34). */
    public const RECEPTION = 'Comité BIS';

    /** @return array<string, Committee> cada comité y sub-comité, por su nombre */
    public static function create(Site $sede): array
    {
        $nodos = [];
        $posicion = 0;

        foreach (self::COMMITTEES as $comite => $subComites) {
            $nodos[$comite] = self::nodo($sede, $comite, null, $posicion++);

            foreach ($subComites as $orden => $subComite) {
                $nodos[$subComite] = self::nodo($sede, $subComite, $nodos[$comite], $orden);
            }
        }

        return $nodos;
    }

    private static function nodo(Site $sede, string $nombre, ?Committee $padre, int $posicion): Committee
    {
        return Committee::create([
            'site_id' => $sede->id,
            'parent_id' => $padre?->id,
            'name' => $nombre,
            'slug' => Str::slug($nombre),
            'position' => $posicion,
            'active' => true,
            'receives_sympathizers' => $nombre === self::RECEPTION,
        ]);
    }
}
