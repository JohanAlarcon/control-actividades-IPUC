<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Una clase de actividad: culto, ensayo, vigilia... Las administra el pastor (D-25). Una clase en
 * uso no se borra: se desactiva, y deja de ofrecerse al proponer sin tocar la historia.
 */
class ActivityType extends Model
{
    use BelongsToSite;

    /** Las que trae cada congregacion al empezar. Despues, el pastor agrega o desactiva. */
    public const DEFAULTS = ['Culto', 'Ensayo', 'Reunión', 'Evento', 'Salida', 'Visita', 'Campaña', 'Convención'];

    protected $fillable = [
        'site_id',
        'name',
        'active',
        'position',
    ];

    protected $attributes = [
        'active' => true,
        'position' => 0,
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'position' => 'integer',
        ];
    }

    public static function createDefaultsFor(Site $sede): void
    {
        foreach (self::DEFAULTS as $posicion => $nombre) {
            self::create(['site_id' => $sede->id, 'name' => $nombre, 'position' => $posicion]);
        }
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
}
