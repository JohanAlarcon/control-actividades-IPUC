<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCommitteeScope;
use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use RuntimeException;

class Approval extends Model
{
    use HasFactory, BelongsToSite, BelongsToCommitteeScope;

    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';

    public const LEVEL_PRESIDENT = 'president';
    public const LEVEL_PASTOR = 'pastor';

    public const TYPE_ACTIVITY = 'activity';
    public const TYPE_EXPENSE = 'expense';
    public const TYPE_TASK = 'task';
    public const TYPE_DATE_CHANGE = 'date_change';
    public const TYPE_NEW_MEMBER = 'new_member';

    protected $fillable = [
        'site_id',
        'committee_id',
        'approvable_type',
        'approvable_id',
        'type',
        'level',
        'escalated_by_user_id',
        'escalated_at',
        'status',
        'title',
        'detail',
        'amount',
        'requested_by_user_id',
        'requested_at',
        'resolved_by_user_id',
        'resolved_at',
        'reason',
        'transferred_from_user_id',
        'transferred_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'requested_at' => 'datetime',
            'escalated_at' => 'datetime',
            'resolved_at' => 'datetime',
            'transferred_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Approval $approval) {
            // RF-018: ningun gasto llega al pastor. ApprovalFlow nunca arma este par; aqui se
            // cierra la puerta por si alguien crea la fila por otro camino (data-model §5).
            if ($approval->type === self::TYPE_EXPENSE && $approval->level === self::LEVEL_PASTOR) {
                throw new RuntimeException('Un gasto no llega al pastor: lo cierra el presidente del comité (RF-018).');
            }

            if ($approval->status === self::REJECTED && blank($approval->reason)) {
                throw new RuntimeException('Un rechazo sin motivo no se guarda: quien propuso tiene que saber por qué (RF-020).');
            }
        });

        static::updating(function (Approval $approval) {
            if ($approval->getOriginal('status') !== self::PENDING) {
                throw new RuntimeException(
                    'Una solicitud ya resuelta no se edita. Para corregirla hay que registrar una decisión nueva.'
                );
            }
        });

        static::deleting(function () {
            throw new RuntimeException('Las decisiones no se borran: quedan en la historia.');
        });
    }

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function escalatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_by_user_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }
}
