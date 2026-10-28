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

            if ($scope->isActive()) {
                $query->getModel()->applyCommitteeScope($query, $scope);
            }
        });
    }

    /**
     * El filtro de siempre: el comite de la fila, dentro del ambito. Un modelo que llega a su comite
     * de otra forma —los miembros, por sus pertenencias— lo sobrescribe.
     */
    public function applyCommitteeScope(Builder $query, CurrentScope $scope): void
    {
        $query->whereIn($this->getTable().'.'.$this->committeeScopeColumn(), $scope->committeeIds());
    }

    /** El modelo que no guarde el comite en committee_id sobrescribe esto. */
    public function committeeScopeColumn(): string
    {
        return 'committee_id';
    }
}
