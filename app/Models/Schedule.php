<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCommitteeScope;
use App\Models\Concerns\BelongsToSite;
use App\Support\Scope\CurrentScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Un cronograma (D-35): el general de cada año, uno por sede, o uno interno de un comité o
 * sub-comité —«Módulo 1, enero a marzo»—. Las actividades de los dos van en la misma tabla.
 */
class Schedule extends Model
{
    use BelongsToCommitteeScope, BelongsToSite, HasFactory;

    public const GENERAL = 'general';

    public const INTERNAL = 'internal';

    protected $fillable = ['site_id', 'committee_id', 'kind', 'year', 'name', 'starts_on', 'ends_on', 'active'];

    protected function casts(): array
    {
        return ['year' => 'integer', 'starts_on' => 'date', 'ends_on' => 'date', 'active' => 'boolean'];
    }

    /**
     * El general no es de ningún comité sino de la sede, y lo ve todo cargo: lo que lleva sale por
     * la excepción 1. Los internos, solo su comité, sus ascendientes y el pastor (RF-086).
     */
    public function applyCommitteeScope(Builder $query, CurrentScope $scope): void
    {
        $query->where(fn ($cuales) => $cuales
            ->where('schedules.kind', self::GENERAL)
            ->orWhereIn('schedules.committee_id', $scope->committeeIds()));
    }

    /** El cronograma general de ese año en la sede. El primero que lo necesita, lo crea. */
    public static function generalOf(int $sede, int $anio): self
    {
        return static::firstOrCreate(
            ['site_id' => $sede, 'kind' => self::GENERAL, 'year' => $anio],
            ['starts_on' => "{$anio}-01-01", 'ends_on' => "{$anio}-12-31"],
        );
    }

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
}
