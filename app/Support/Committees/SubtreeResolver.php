<?php

namespace App\Support\Committees;

use App\Models\Committee;
use Illuminate\Support\Facades\DB;

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
    public function idsFor(int $committeeId): array
    {
        $nietos = DB::table('committees as hijos')
            ->select('hijos.id')
            ->where('hijos.parent_id', $committeeId);

        return Committee::query()
            ->withoutGlobalScopes()
            ->where('id', $committeeId)
            ->orWhere('parent_id', $committeeId)
            ->orWhereIn('parent_id', $nietos)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
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
