<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** El lema de un año de la sede, con su cita bíblica (RF-082). Los de años anteriores se conservan. */
class SiteMotto extends Model
{
    use BelongsToSite;

    protected $table = 'site_mottos';

    protected $fillable = ['site_id', 'year', 'text', 'citation', 'recorded_by_user_id'];

    protected function casts(): array
    {
        return ['year' => 'integer'];
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
