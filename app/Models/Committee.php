<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCommitteeScope;
use App\Models\Concerns\BelongsToSite;
use App\Support\Sympathizers\Assignments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class Committee extends Model
{
    use BelongsToCommitteeScope, BelongsToSite, HasFactory;

    public const MAX_DEPTH = 2;

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
        'receives_sympathizers',
        'logo_path',
        'motto',
        'objectives',
        'identity_updated_by_user_id',
        'identity_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'depth' => 'integer',
            'position' => 'integer',
            'active' => 'boolean',
            'receives_sympathizers' => 'boolean',
            'identity_updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Committee $committee) {
            $committee->depth = $committee->calculateDepth();
            $committee->guardUniqueName();
        });
        static::updated(function (Committee $committee) {
            if ($committee->wasChanged('active') && ! $committee->active) {
                Assignments::returnToReception($committee);
            }
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

    /** El logo pasa por una ruta con el filtro (RF-083); la versión evita que el celular muestre el anterior. */
    public function logoUrl(): ?string
    {
        return $this->logo_path ? "/committees/{$this->id}/logo?v={$this->identity_updated_at?->timestamp}" : null;
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
     * un sub-comite daria nivel 3, que la constitucion no permite (D-31, CL-17).
     */
    private function calculateDepth(): int
    {
        if ($this->parent_id === null) {
            return 1;
        }

        $parent = self::withoutGlobalScopes()->findOrFail($this->parent_id);

        if ($parent->depth >= self::MAX_DEPTH) {
            throw ValidationException::withMessages([
                'parent_id' => 'La estructura llega hasta dos niveles: comité y sub-comité. Un sub-comité no se puede dividir en otros grupos.',
            ]);
        }

        return $parent->depth + 1;
    }

    /** Un comite se filtra por su propio id, no por una llave foranea. */
    public function committeeScopeColumn(): string
    {
        return 'id';
    }

    /**
     * BIS, el comite que recibe a los simpatizantes de la sede (D-34). Se busca fuera del filtro del
     * cargo porque a el vuelven los simpatizantes de un comite que se inactiva (CL-29), pero trae
     * solo su numero, su sede y su nombre: ningun dato de lo que hace ese comite.
     */
    public static function receivingSympathizers(int $sede): ?self
    {
        return static::withoutGlobalScope('committee')
            ->where('site_id', $sede)
            ->where('receives_sympathizers', true)
            ->where('active', true)
            ->first(['id', 'site_id', 'name']);
    }

    /**
     * La excepcion 2 del principio I (constitucion 3.0.0, D-34): la lista para asignar
     * simpatizantes. Trae el numero y el nombre de los comites y sub-comites activos de la sede, y
     * nada mas de ellos. Solo lee. BisScopeTest la vigila con una lista blanca.
     */
    public static function forAssignment(): Collection
    {
        return static::withoutGlobalScope('committee')
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Committee $comite) => ['id' => $comite->id, 'name' => $comite->name]);
    }
}
