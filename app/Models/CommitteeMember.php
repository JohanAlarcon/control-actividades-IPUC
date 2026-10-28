<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCommitteeScope;
use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * La pertenencia de un miembro a un comité o sub-comité, con su función ahí (D-32, RF-084): de la
 * directiva —con su cargo—, maestro, auxiliar o integrante. Quitar a alguien de la lista cierra la
 * pertenencia con su fecha; nunca la borra, y su historia se conserva.
 */
class CommitteeMember extends Model
{
    use BelongsToCommitteeScope, BelongsToSite, HasFactory;

    protected $table = 'committee_member';

    public const ROLES = ['board', 'teacher', 'assistant', 'member'];

    public const BOARD_POSITIONS = ['president', 'secretary', 'treasurer', 'vocal'];

    protected $fillable = ['site_id', 'member_id', 'committee_id', 'role', 'board_position', 'since', 'ended_at'];

    protected $attributes = ['role' => 'member'];

    protected function casts(): array
    {
        return [
            'since' => 'date',
            'ended_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(fn () => throw new RuntimeException('Una pertenencia no se borra: se cierra con su fecha, y su historia se conserva.'));
        static::saving(function (CommitteeMember $pertenencia) {
            if ($pertenencia->role !== 'board') {
                $pertenencia->board_position = null;
            }
        });
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    /** Las que siguen abiertas: quien hoy está en la lista del comité. */
    public function scopeCurrent(Builder $query): void
    {
        $query->whereNull('committee_member.ended_at');
    }
}
