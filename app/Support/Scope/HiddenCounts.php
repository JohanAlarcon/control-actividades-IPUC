<?php

namespace App\Support\Scope;

use App\Models\Committee;

/**
 * RF-009: cuanta informacion de la congregacion queda fuera del alcance.
 *
 * Solo CANTIDADES. Decir "hay 3 comites que usted no ve" es lo que pide el
 * requisito; decir cuales seria filtrar datos. Por eso vive aqui y no en un
 * controlador: es el unico lugar autorizado a contar lo que no se ve.
 */
class HiddenCounts
{
    public function __construct(private readonly CurrentScope $scope) {}

    public function all(): array
    {
        if (! $this->scope->isActive() || $this->scope->isPastor()) {
            return ['committees' => 0];
        }

        $visibles = count($this->scope->committeeIds());
        $total = Committee::withoutGlobalScope('committee')->count();

        return [
            'committees' => max(0, $total - $visibles),
        ];
    }
}
