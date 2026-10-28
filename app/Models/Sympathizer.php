<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCommitteeScope;
use App\Models\Concerns\BelongsToSite;
use App\Support\People\PersonAge;
use App\Support\Scope\CurrentScope;
use App\Support\Sympathizers\CommitmentLevel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use RuntimeException;

/**
 * Alguien que visito la iglesia y se acompaña (data-model §3). Su ficha de seguimiento la ven solo
 * el pastor y quien preside el comite que acompaña a los simpatizantes (RF-040).
 */
class Sympathizer extends Model
{
    use BelongsToCommitteeScope, BelongsToSite, HasFactory;

    public const ARRIVAL_SOURCES = ['invitation', 'refam', 'campaign', 'family', 'neighbor', 'media'];

    /** La etapa en que se bautizo en el nombre de Jesucristo, y la ultima del camino (RF-042). */
    public const BAPTISM_STAGE = 4;

    public const LAST_STAGE = 5;

    protected $fillable = [
        'site_id',
        'committee_id',
        'name',
        'birth_date',
        'age_reported',
        'age_reported_at',
        'gender',
        'phone',
        'neighborhood',
        'arrival_source',
        'invited_by_user_id',
        'invited_by_name',
        'first_contact_at',
        'stage',
        'converted_member_id',
        'converted_at',
        'data_consent',
        'data_consent_at',
        'data_consent_means',
    ];

    protected $attributes = [
        'stage' => 1,
        'data_consent' => false,
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'age_reported' => 'integer',
            'age_reported_at' => 'date',
            'first_contact_at' => 'date',
            'stage' => 'integer',
            'converted_at' => 'date',
            'data_consent' => 'boolean',
            'data_consent_at' => 'date',
        ];
    }

    /** Si se aleja y vuelve, su historia no se reinicia: la fila nunca se borra (CL-10). */
    protected static function booted(): void
    {
        static::deleting(fn () => throw new RuntimeException('La ficha de un simpatizante no se borra: su historia se conserva.'));
        static::saving(function (Sympathizer $persona) {
            if (($persona->age_reported === null) !== ($persona->age_reported_at === null)) {
                throw new RuntimeException('La edad anotada se guarda con el día en que se anotó: sin ese día, la edad no se puede calcular.');
            }
        });
    }

    /**
     * La excepcion 2 del principio I (constitucion 3.0.0, D-34): un cargo de BIS ve a todos los
     * simpatizantes de la sede, para recibirlos y asignarlos. Los demas cargos ven los asignados a su
     * ambito, con el filtro de siempre, y el pastor ve todo. Solo los simpatizantes: BIS no ve por
     * esto ningun miembro ni actividad ajena.
     */
    public function applyCommitteeScope(Builder $query, CurrentScope $scope): void
    {
        if (! $scope->isReception()) {
            $query->whereIn('sympathizers.committee_id', $scope->committeeIds());
        }
    }

    /** Trae lo necesario para calcular el compromiso, en la misma consulta (R-07). */
    public function scopeWithCommitment(Builder $query): void
    {
        $query->withCount(['visits as recent_visits_count' => fn (Builder $visitas) => $visitas
            ->where('visited_at', '>=', CommitmentLevel::recentSince()->toDateString())])
            ->withMax('visits', 'visited_at');
    }

    public function age(): ?int
    {
        return PersonAge::of($this->birth_date, $this->age_reported, $this->age_reported_at);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(SympathizerStage::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(SympathizerVisit::class);
    }

    public function approvals(): MorphMany
    {
        return $this->morphMany(Approval::class, 'approvable');
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    /** Por que comites paso, en orden (RF-097). */
    public function assignments(): HasMany
    {
        return $this->hasMany(SympathizerAssignment::class)->orderBy('assigned_at')->orderBy('id');
    }
}
