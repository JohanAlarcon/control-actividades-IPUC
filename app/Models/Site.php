<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    use HasFactory;

    /** Toda congregacion empieza con las clases de actividad de siempre (D-25). */
    protected static function booted(): void
    {
        static::created(fn (Site $sede) => ActivityType::createDefaultsFor($sede));
    }

    protected $fillable = [
        'name',
        'city',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function committees(): HasMany
    {
        return $this->hasMany(Committee::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
