<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\Scope\CurrentScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use RuntimeException;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'site_id',
        'name',
        'username',
        'email',
        'password',
        'must_change_password',
        'is_pastor',
        'active',
    ];

    /** Para que el modelo recien creado ya sepa su estado, sin tener que releerlo. */
    protected $attributes = [
        'must_change_password' => true,
        'is_pastor' => false,
        'active' => true,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'must_change_password' => 'boolean',
            'is_pastor' => 'boolean',
            'active' => 'boolean',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** Una cuenta nunca se borra: se le retira el acceso y su nombre queda en la historia (RN-09). */
    protected static function booted(): void
    {
        static::deleting(function () {
            throw new RuntimeException('Una cuenta no se borra: se le retira el acceso. Su nombre tiene que quedar en la historia.');
        });
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(CommitteeUser::class);
    }

    public function currentRoles(): HasMany
    {
        return $this->roles()->whereNull('ended_at');
    }

    /**
     * Una cuenta de otra sede, pedida por su numero en la direccion, responde igual
     * que una que no existe (RF-008). Esta tabla no lleva el filtro de sede porque la
     * entrada al sistema tiene que buscar a la persona antes de saber de que sede es.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('site_id', app(CurrentScope::class)->siteId())
            ->where($field ?? $this->getRouteKeyName(), $value)
            ->firstOrFail();
    }
}
