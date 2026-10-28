<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCommitteeScope;
use App\Models\Concerns\BelongsToSite;
use App\Support\People\PersonAge;
use App\Support\Scope\CurrentScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

/** La ficha de un miembro de la congregacion (data-model §2). */
class Member extends Model
{
    use BelongsToCommitteeScope, BelongsToSite, HasFactory;

    public const MARITAL_STATUSES = ['single', 'married', 'common_law', 'separated', 'divorced', 'widowed'];

    /** Lo que un menor de dieciocho años no puede figurar (RF-032). */
    public const ADULT_ONLY = ['married', 'common_law', 'separated', 'divorced', 'widowed'];

    public const CONSENT_MEANS = ['signature', 'verbal', 'message'];

    protected $fillable = [
        'site_id',
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
        'notes',
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
        // El codigo lo pone el sistema en orden de registro, y no cambia (S-12).
        static::created(fn (Member $persona) => $persona->forceFill(['code' => sprintf('M-%04d', $persona->id)])->saveQuietly());
        static::saving(function (Member $persona) {
            if (($persona->age_reported === null) !== ($persona->age_reported_at === null)) {
                throw new RuntimeException('La edad anotada se guarda con el día en que se anotó: sin ese día, la edad no se puede calcular.');
            }
            if ($persona->adult_confirmed && $persona->adult_confirmed_at === null) {
                throw new RuntimeException('La confirmación de la mayoría de edad se guarda con su fecha.');
            }
        });
    }

    /**
     * Un miembro entra al ambito por sus pertenencias vigentes (D-32): lo ve cada comite donde esta,
     * y nadie mas. El pastor ve el padron completo de su sede, tambien a quien todavia no esta en
     * ningun comite (CL-03, CL-27).
     */
    public function applyCommitteeScope(Builder $query, CurrentScope $scope): void
    {
        if ($scope->isPastor()) {
            return;
        }

        $query->whereExists(fn ($pertenencia) => $pertenencia
            ->from('committee_member')
            ->whereColumn('committee_member.member_id', 'members.id')
            ->whereNull('committee_member.ended_at')
            ->whereIn('committee_member.committee_id', $scope->committeeIds()));
    }

    public function age(): ?int
    {
        return PersonAge::of($this->birth_date, $this->age_reported, $this->age_reported_at);
    }

    /** Sus pertenencias, que pasan por el filtro: cada cargo ve solo las de su ambito. */
    public function memberships(): HasMany
    {
        return $this->hasMany(CommitteeMember::class);
    }

    /**
     * La excepcion 4 del principio I (constitucion 3.0.0, D-33): el buscador del padron, para que
     * un comite escoja a quien agregar a su lista. Retira el filtro del cargo, nunca el de la sede,
     * y trae solo lo necesario para decir el nombre y la edad: ni comites, ni telefono, ni barrio.
     * Busca por el comienzo de cada palabra: «ma» trae a Marleny, no a todas las «Hermanas». Solo
     * lee. MemberPickerTest lo vigila con una lista blanca.
     */
    public static function forPicker(?string $nombre = null): Builder
    {
        $texto = addcslashes((string) $nombre, '%_\\');

        return static::query()
            ->withoutGlobalScope('committee')
            ->where('active', true)
            ->when($nombre !== null, fn (Builder $consulta) => $consulta->where(fn (Builder $palabra) => $palabra
                ->where('name', 'like', $texto.'%')
                ->orWhere('name', 'like', '% '.$texto.'%')))
            ->orderBy('name')
            ->select(['id', 'site_id', 'name', 'birth_date', 'age_reported', 'age_reported_at']);
    }
}
