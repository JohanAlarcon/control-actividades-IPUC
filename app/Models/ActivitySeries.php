<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCommitteeScope;
use App\Models\Concerns\BelongsToSite;
use App\Support\Scope\CurrentScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Una actividad que se repite (D-38): cada semana, cada quince días o cada mes, entre dos fechas.
 * Sus fechas son actividades sueltas, y cada una se mueve o se cancela sola (CL-30).
 */
class ActivitySeries extends Model
{
    use BelongsToCommitteeScope, BelongsToSite;

    public const FREQUENCIES = ['weekly', 'biweekly', 'monthly'];

    protected $table = 'activity_series';

    protected $fillable = ['site_id', 'schedule_id', 'frequency', 'starts_on', 'ends_on'];

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date'];
    }

    /** La serie no guarda su comité: es el de su cronograma, que ya pasa por el filtro. */
    public function applyCommitteeScope(Builder $query, CurrentScope $scope): void
    {
        $query->whereIn('activity_series.schedule_id', Schedule::query()->select('schedules.id'));
    }
}
