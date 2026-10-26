<?php

namespace App\Support\Committees;

use App\Models\Committee;
use Illuminate\Support\Collection;

/**
 * Los comites del alcance del cargo, en orden de arbol: cada uno seguido de los que cuelgan de el.
 * Sirven para filtrar y para llenar desplegables, y nunca traen uno ajeno: van con el filtro puesto.
 */
class CommitteeOptions
{
    /** @return array<int, array{id: int, name: string, depth: int}> */
    public static function forScope(): array
    {
        $comites = Committee::query()->where('active', true)->orderBy('position')->orderBy('name')->get(['id', 'name', 'parent_id', 'depth']);
        $visibles = $comites->pluck('id')->all();

        $rama = function (Collection $nivel) use (&$rama, $comites): array {
            return $nivel->flatMap(fn (Committee $comite) => [
                ['id' => $comite->id, 'name' => $comite->name, 'depth' => $comite->depth],
                ...$rama($comites->where('parent_id', $comite->id)),
            ])->all();
        };

        return $rama($comites->filter(fn (Committee $comite) => ! in_array($comite->parent_id, $visibles, true)));
    }
}
