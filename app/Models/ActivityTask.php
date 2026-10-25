<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityTask extends Model
{
    use HasFactory, BelongsToSite;

    protected $fillable = [
        'site_id',
        'activity_id',
        'description',
        'done',
        'assigned_to_name',
        'done_at',
    ];

    protected function casts(): array
    {
        return [
            'done' => 'boolean',
            'done_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn (ActivityTask $task) => $task->activity?->refreshProgress());
        static::deleted(fn (ActivityTask $task) => $task->activity?->refreshProgress());
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
