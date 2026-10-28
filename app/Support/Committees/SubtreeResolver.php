<?php

namespace App\Support\Committees;

use App\Models\Committee;

/**
 * El ambito de un cargo es su nodo y todos sus descendientes.
 *
 * La constitucion limita la estructura a dos niveles, asi que el sub-arbol se
 * resuelve con una sola consulta: el nodo y sus hijos. Sin recursion y sin CTE,
 * que es lo que mantiene el techo de cinco consultas.
 */
class SubtreeResolver
{
    /** @return array<int> */
    public function idsFor(int $committeeId, int $depth = 1): array
    {
        // Un sub-comite es el ultimo nivel y nunca tiene hijos: no hace falta preguntarle a la base.
        if ($depth >= Committee::MAX_DEPTH) {
            return [$committeeId];
        }

        return Committee::query()
            ->withoutGlobalScopes()
            ->where('id', $committeeId)
            ->orWhere('parent_id', $committeeId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * El comite de primer nivel del que cuelga un nodo: su presidente es quien revisa lo
     * que se propone ahi (HU-03, escenario 4). Devuelve solo el numero, nunca datos.
     */
    public function rootIdOf(Committee $nodo): int
    {
        return $nodo->parent_id ?? $nodo->id;
    }

    /** Todos los comites de una sede: es el ambito del pastor. */
    public function idsForSite(int $siteId): array
    {
        return Committee::query()
            ->withoutGlobalScopes()
            ->where('site_id', $siteId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
