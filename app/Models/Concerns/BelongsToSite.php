<?php

namespace App\Models\Concerns;

use App\Support\Scope\CurrentScope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Multi-sede desde el primer dia: ninguna consulta puede omitir la sede.
 * El filtro viene puesto, no hay que escribirlo en cada consulta.
 */
trait BelongsToSite
{
    public static function bootBelongsToSite(): void
    {
        static::addGlobalScope('site', function (Builder $query) {
            $scope = app(CurrentScope::class);

            if ($scope->isActive()) {
                $query->where($query->getModel()->getTable().'.site_id', $scope->siteId());
            }
        });

        static::creating(function ($model) {
            $scope = app(CurrentScope::class);

            if ($model->site_id === null && $scope->isActive()) {
                $model->site_id = $scope->siteId();
            }
        });
    }
}
