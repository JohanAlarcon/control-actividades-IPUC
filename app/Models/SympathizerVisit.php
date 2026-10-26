<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Una visita, llamada, estudio o asistencia al culto, con quien la hizo (RF-044). */
class SympathizerVisit extends Model
{
    use BelongsToSite;

    public const TYPES = ['visit', 'call', 'study', 'service'];

    protected $fillable = [
        'site_id',
        'sympathizer_id',
        'visited_at',
        'type',
        'by_user_id',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'date',
        ];
    }

    public function by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'by_user_id');
    }
}
