<?php

namespace App\Support\Scope;

use App\Models\Committee;
use App\Models\Member;

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
            return ['committees' => 0, 'members' => 0];
        }

        $visibles = $this->scope->committeeIds();

        return [
            'committees' => max(0, Committee::withoutGlobalScope('committee')->count() - count($visibles)),
            'members' => Member::withoutGlobalScope('committee')
                ->where('active', true)
                ->where(fn ($fuera) => $fuera->whereNotIn('committee_id', $visibles)->orWhereNull('committee_id'))
                ->count(),
        ];
    }
}
