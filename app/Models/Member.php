<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCommitteeScope;
use App\Models\Concerns\BelongsToSite;
use App\Support\People\PersonAge;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/** La ficha de un miembro de la congregacion (data-model §2). */
class Member extends Model
{
    use BelongsToCommitteeScope, BelongsToSite, HasFactory;

    public const MARITAL_STATUSES = ['single', 'married', 'widowed', 'separated', 'common_law'];

    /** Lo que un menor de dieciocho años no puede figurar (RF-032). */
    public const ADULT_ONLY = ['married', 'widowed', 'separated', 'common_law'];

    public const CONSENT_MEANS = ['signature', 'verbal', 'message'];

    protected $fillable = [
        'site_id',
        'committee_id',
        'name',
        'birth_date',
        'age_reported',
        'age_reported_at',
        'gender',
        'marital_status',
        'baptized',
        'baptized_at',
        'joined_at',
        'phone',
        'neighborhood',
        'adult_confirmed',
        'adult_confirmed_at',
        'profile_pending',
        'data_consent',
        'data_consent_at',
        'data_consent_means',
        'active',
    ];

    protected $attributes = [
        'marital_status' => 'single',
        'baptized' => false,
        'adult_confirmed' => false,
        'profile_pending' => false,
        'data_consent' => false,
        'active' => true,
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'age_reported' => 'integer',
            'age_reported_at' => 'date',
            'baptized' => 'boolean',
            'baptized_at' => 'date',
            'joined_at' => 'date',
            'adult_confirmed' => 'boolean',
            'adult_confirmed_at' => 'date',
            'profile_pending' => 'boolean',
            'data_consent' => 'boolean',
            'data_consent_at' => 'date',
            'active' => 'boolean',
        ];
    }

    /** Una ficha nunca se borra: se retira con active en falso, y su historia conserva el nombre. */
    protected static function booted(): void
    {
        static::deleting(fn () => throw new RuntimeException('Una ficha de miembro no se borra: se retira, y su historia se conserva.'));
        static::saving(function (Member $persona) {
            if (($persona->age_reported === null) !== ($persona->age_reported_at === null)) {
                throw new RuntimeException('La edad anotada se guarda con el día en que se anotó: sin ese día, la edad no se puede calcular.');
            }
            if ($persona->adult_confirmed && $persona->adult_confirmed_at === null) {
                throw new RuntimeException('La confirmación de la mayoría de edad se guarda con su fecha.');
            }
        });
    }

    /** El pastor tambien ve a quien no esta en ningun comite: cuenta en los reportes (CL-03). */
    public function unassignedVisibleToPastor(): bool
    {
        return true;
    }

    public function age(): ?int
    {
        return PersonAge::of($this->birth_date, $this->age_reported, $this->age_reported_at);
    }

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }
}
