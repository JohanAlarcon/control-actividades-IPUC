<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCommitteeScope;
use App\Models\Concerns\BelongsToSite;
use App\Support\Activities\ActivityStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    use BelongsToCommitteeScope, BelongsToSite, HasFactory;

    public const PENDING = 'pending';

    public const APPROVED = 'approved';

    public const IN_PROGRESS = 'in_progress';

    public const COMPLETED = 'completed';

    public const REJECTED = 'rejected';

    /** Las marcas de color del cronograma de la sede (RF-116): rojo la primera, naranja las otras dos. */
    public const HIGHLIGHTS = ['special_day', 'district', 'national'];

    protected $fillable = [
        'site_id',
        'committee_id',
        'annual_plan_id',
        'schedule_id',
        'series_id',
        'title',
        'theme',
        'highlight',
        'description',
        'scheduled_at',
        'ends_at',
        'location',
        'responsible_name',
        'activity_type_id',
        'status',
        'budget_amount',
        'proposed_by_user_id',
        'proposed_at',
        'approved_by_user_id',
        'approved_at',
        'rejection_reason',
        'completed_at',
        'completed_by_user_id',
        'cancelled_at',
        'cancelled_by_user_id',
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
            'ends_at' => 'datetime',
            'proposed_at' => 'datetime',
            'approved_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'progress' => 'integer',
            'budget_amount' => 'decimal:2',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    /** Solo lo del cronograma general (D-35): lo interno de cada comité no se comparte ni se cruza. */
    public function scopeInGeneralSchedule(Builder $query): void
    {
        $query->whereIn('activities.schedule_id', Schedule::query()->where('schedules.kind', Schedule::GENERAL)->select('schedules.id'));
    }

    public function activityType(): BelongsTo
    {
        return $this->belongsTo(ActivityType::class);
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
        $this->status = ActivityStatus::afterTasks($this, $total);

        if ($this->status === self::COMPLETED) {
            $this->completed_at ??= now();
        }

        $this->saveQuietly();
    }

    /**
     * La excepcion 1 del principio I (constitucion 3.0.0; RF-010, D-15, D-36): el cronograma
     * compartido. Retira el filtro del cargo, nunca el de la sede, y trae a mano solo los seis
     * datos que se comparten —titulo, fecha, hora de inicio y de fin, lugar, estado y el nombre del
     * comite— mas el numero para abrirla. Nunca la descripcion, el presupuesto, las tareas, el tema
     * ni la marca (P-09), y nada de los cronogramas internos (D-35). Solo lee: no sirve de base para modificar, borrar ni descargar, y no da
     * permiso de nada; la policy sigue negando toda escritura sobre lo ajeno. SharedCalendarTest
     * lo vigila con una lista blanca.
     */
    public static function forSharedCalendar(): Builder
    {
        return static::query()
            ->withoutGlobalScope('committee')
            ->join('committees', 'committees.id', '=', 'activities.committee_id')
            ->inGeneralSchedule()
            ->select([
                'activities.id',
                'activities.title',
                'activities.scheduled_at',
                'activities.ends_at',
                'activities.location',
                'activities.status',
                'committees.name as committee_name',
            ]);
    }
}
