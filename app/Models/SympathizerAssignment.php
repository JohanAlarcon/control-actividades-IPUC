<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * Por qué comités pasó un simpatizante (RF-097, CL-29): cada asignación y cada reasignación, con
 * quién la hizo —nadie, cuando el sistema lo devolvió a BIS— y cuándo. Solo se agregan filas.
 */
class SympathizerAssignment extends Model
{
    use BelongsToSite;

    protected $fillable = ['site_id', 'sympathizer_id', 'committee_id', 'assigned_by_user_id', 'assigned_at'];

    protected function casts(): array
    {
        return ['assigned_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new RuntimeException('Una asignación no se cambia: la que sigue se agrega aparte.'));
        static::deleting(fn () => throw new RuntimeException('Una asignación no se borra: es la historia del simpatizante.'));
    }

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }
}
