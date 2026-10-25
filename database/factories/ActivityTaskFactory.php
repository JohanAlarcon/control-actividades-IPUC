<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\ActivityTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityTask>
 */
class ActivityTaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'activity_id' => Activity::factory(),
            'site_id' => fn (array $attrs) => Activity::find($attrs['activity_id'])?->site_id,
            'description' => 'Pasar el repertorio a cada integrante.',
            'done' => false,
            'assigned_to_name' => null,
        ];
    }

    public function forActivity(Activity $activity): static
    {
        return $this->state(fn () => [
            'activity_id' => $activity->id,
            'site_id' => $activity->site_id,
        ]);
    }

    public function done(): static
    {
        return $this->state(fn () => ['done' => true, 'done_at' => now()]);
    }
}
