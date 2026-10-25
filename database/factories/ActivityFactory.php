<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Committee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'committee_id' => Committee::factory(),
            'site_id' => fn (array $attrs) => Committee::find($attrs['committee_id'])?->site_id,
            'annual_plan_id' => null,
            'title' => 'Ensayo general del coro',
            'description' => 'Ensayo del repertorio para el culto del domingo.',
            'scheduled_at' => now()->addWeek(),
            'location' => 'Templo',
            'type' => 'rehearsal',
            'status' => Activity::PENDING,
            'budget_amount' => null,
            'proposed_by_user_id' => User::factory(),
            'proposed_at' => now(),
        ];
    }

    public function forCommittee(Committee $committee): static
    {
        return $this->state(fn () => [
            'committee_id' => $committee->id,
            'site_id' => $committee->site_id,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => Activity::APPROVED,
            'approved_at' => now(),
        ]);
    }
}
