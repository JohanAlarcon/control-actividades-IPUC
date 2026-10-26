<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Una etapa alcanzada, con su fecha. Solo se agregan: la historia no se reinicia (RF-042, CL-10). */
class SympathizerStage extends Model
{
    use BelongsToSite;

    protected $fillable = [
        'site_id',
        'sympathizer_id',
        'stage',
        'reached_at',
        'recorded_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'stage' => 'integer',
            'reached_at' => 'date',
        ];
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
