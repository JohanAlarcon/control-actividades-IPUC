<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class CommitteeUser extends Model
{
    use HasFactory, BelongsToSite;

    public const PRESIDENT = 'president';
    public const SECRETARY = 'secretary';
    public const TREASURER = 'treasurer';
    public const LEADER = 'leader';

    /** Cargos que solo existen en un comité, nunca en un sub-comité ni en un equipo. */
    public const TOP_LEVEL_ROLES = [self::PRESIDENT, self::SECRETARY, self::TREASURER];

    protected $table = 'committee_user';

    protected $fillable = [
        'site_id',
        'user_id',
        'committee_id',
        'role',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'ended_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (CommitteeUser $cargo) {
            $cargo->guardRoleFitsLevel();
            $cargo->guardSingleHolder();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->whereNull('ended_at');
    }

    public function isCurrent(): bool
    {
        return $this->ended_at === null;
    }

    private function guardRoleFitsLevel(): void
    {
        $committee = Committee::withoutGlobalScopes()->findOrFail($this->committee_id);
        $esDelPrimerNivel = in_array($this->role, self::TOP_LEVEL_ROLES, true);

        if ($esDelPrimerNivel && $committee->depth !== 1) {
            throw ValidationException::withMessages([
                'role' => 'Presidente, secretario y tesorero son cargos de un comité. Un sub-comité o un equipo solo tiene líder.',
            ]);
        }

        if ($this->role === self::LEADER && $committee->depth === 1) {
            throw ValidationException::withMessages([
                'role' => 'Un comité lo encabeza su presidente, no un líder.',
            ]);
        }
    }

    private function guardSingleHolder(): void
    {
        if ($this->ended_at !== null) {
            return;
        }

        $yaHayTitular = self::query()
            ->where('committee_id', $this->committee_id)
            ->where('role', $this->role)
            ->whereNull('ended_at')
            ->when($this->exists, fn (Builder $q) => $q->whereKeyNot($this->getKey()))
            ->exists();

        if ($yaHayTitular) {
            throw ValidationException::withMessages([
                'role' => 'Ese cargo ya tiene titular vigente. Primero hay que cerrar el anterior.',
            ]);
        }
    }
}
