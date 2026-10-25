<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    use HasFactory;

    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const IN_PROGRESS = 'in_progress';
    public const COMPLETED = 'completed';
    public const REJECTED = 'rejected';

    protected $fillable = [
        'site_id',
        'committee_id',
        'annual_plan_id',
        'title',
        'description',
        'scheduled_at',
        'location',
        'type',
        'status',
        'budget_amount',
        'proposed_by_user_id',
        'proposed_at',
        'approved_by_user_id',
        'approved_at',
        'rejection_reason',
        'completed_at',
    ];

    /** Para que el modelo recien creado ya sepa su estado, sin tener que releerlo. */
    protected $attributes = [
        'status' => self::PENDING,
        'progress' => 0,
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'proposed_at' => 'datetime',
            'approved_at' => 'datetime',
            'completed_at' => 'datetime',
            'progress' => 'integer',
            'budget_amount' => 'decimal:2',
        ];
    }

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    public function annualPlan(): BelongsTo
    {
        return $this->belongsTo(AnnualPlan::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ActivityTask::class);
    }

    /** Lo que venia en el plan del año no necesita aprobación nueva (RN-03). */
    public function isFromAnnualPlan(): bool
    {
        return $this->annual_plan_id !== null;
    }

    /**
     * El avance sale de las tareas cumplidas y nadie lo escribe a mano (RF-027).
     * Sin tareas es cero: no hay nada que medir todavia.
     */
    public function refreshProgress(): void
    {
        $total = $this->tasks()->count();
        $cumplidas = $this->tasks()->where('done', true)->count();

        $this->progress = $total === 0 ? 0 : (int) round($cumplidas / $total * 100);
        $this->saveQuietly();
    }
}
