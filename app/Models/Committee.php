<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Committee extends Model
{
    use HasFactory;

    public const MAX_DEPTH = 3;

    protected $fillable = [
        'site_id',
        'parent_id',
        'name',
        'slug',
        'mission',
        'color',
        'icon',
        'position',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'depth' => 'integer',
            'position' => 'integer',
            'active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Committee $committee) {
            $committee->depth = $committee->calculateDepth();
            $committee->guardUniqueName();
        });
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function isTopLevel(): bool
    {
        return $this->parent_id === null;
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * MySQL no aplica un indice unico cuando una de sus columnas es nula, asi que
     * entre comites del primer nivel la restriccion hay que verificarla aqui.
     */
    private function guardUniqueName(): void
    {
        $repetido = self::withoutGlobalScopes()
            ->where('site_id', $this->site_id)
            ->where('parent_id', $this->parent_id)
            ->where('name', $this->name)
            ->when($this->exists, fn ($q) => $q->whereKeyNot($this->getKey()))
            ->exists();

        if ($repetido) {
            throw ValidationException::withMessages([
                'name' => 'Ya hay un comité con ese nombre en el mismo lugar de la estructura.',
            ]);
        }
    }

    /**
     * La profundidad sale del padre y nunca se escribe a mano. Un nodo colgado de
     * un equipo daria nivel 4, que la constitucion no permite.
     */
    private function calculateDepth(): int
    {
        if ($this->parent_id === null) {
            return 1;
        }

        $parent = self::withoutGlobalScopes()->findOrFail($this->parent_id);

        if ($parent->depth >= self::MAX_DEPTH) {
            throw ValidationException::withMessages([
                'parent_id' => 'La estructura llega hasta tres niveles: comité, sub-comité y equipo. Un equipo no se puede dividir más.',
            ]);
        }

        return $parent->depth + 1;
    }
}
