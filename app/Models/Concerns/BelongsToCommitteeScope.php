<?php

namespace App\Models\Concerns;

use App\Support\Scope\CurrentScope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Principio I, NO NEGOCIABLE: cada cargo ve su nodo y sus descendientes.
 *
 * El filtro se aplica solo. Ninguna consulta de este proyecto escribe
 * "where committee_id" a mano, y si alguna lo hiciera estaria saltandose esto.
 */
trait BelongsToCommitteeScope
{
    public static function bootBelongsToCommitteeScope(): void
    {
        static::addGlobalScope('committee', function (Builder $query) {
            $scope = app(CurrentScope::class);

            if (! $scope->isActive()) {
                return;
            }

            $columna = $query->getModel()->getTable().'.'.$query->getModel()->committeeScopeColumn();

            $query->where(function (Builder $filtro) use ($query, $scope, $columna) {
                $filtro->whereIn($columna, $scope->committeeIds());

                if ($scope->isPastor() && $query->getModel()->unassignedVisibleToPastor()) {
                    $filtro->orWhereNull($columna);
                }
            });
        });
    }

    /**
     * Si el pastor ve tambien las filas sin comite. Solo las fichas de miembros las tienen: quien no
     * esta en ningun comite sigue contando en los reportes de la congregacion (CL-03).
     */
    public function unassignedVisibleToPastor(): bool
    {
        return false;
    }

    /** El modelo que no guarde el comite en committee_id sobrescribe esto. */
    public function committeeScopeColumn(): string
    {
        return 'committee_id';
    }
}
