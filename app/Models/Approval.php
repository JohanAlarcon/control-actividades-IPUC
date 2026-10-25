<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use RuntimeException;

class Approval extends Model
{
    use HasFactory;

    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';

    public const LEVEL_PRESIDENT = 'president';
    public const LEVEL_PASTOR = 'pastor';

    protected $fillable = [
        'site_id',
        'committee_id',
        'approvable_type',
        'approvable_id',
        'type',
        'level',
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
            'resolved_at' => 'datetime',
            'transferred_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
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

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }
}
