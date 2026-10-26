<?php

namespace App\Support\Committees;

use App\Models\Committee;

/**
 * El ambito de un cargo es su nodo y todos sus descendientes.
 *
 * La constitucion limita la estructura a tres niveles, asi que el sub-arbol se
 * resuelve con una sola consulta: el nodo, sus hijos, y los hijos de sus hijos.
 * Sin recursion y sin CTE, que es lo que mantiene el techo de cinco consultas.
 */
class SubtreeResolver
{
    /** @return array<int> */
    public function idsFor(int $committeeId, int $depth = 1): array
    {
        // Un equipo es el ultimo nivel y nunca tiene hijos: no hace falta preguntarle a la base.
        if ($depth >= Committee::MAX_DEPTH) {
            return [$committeeId];
        }

        $hijos = Committee::query()
            ->withoutGlobalScopes()
            ->select('id')
            ->where('parent_id', $committeeId);

        return Committee::query()
            ->withoutGlobalScopes()
            ->where('id', $committeeId)
            ->orWhere('parent_id', $committeeId)
            ->orWhereIn('parent_id', $hijos)
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
        return match ($nodo->depth) {
            1 => $nodo->id,
            2 => $nodo->parent_id,
            default => (int) Committee::query()->withoutGlobalScopes()->whereKey($nodo->parent_id)->value('parent_id'),
        };
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
