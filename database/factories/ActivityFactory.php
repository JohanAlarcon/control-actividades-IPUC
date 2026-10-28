<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\Committee;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'committee_id' => Committee::factory(),
            'site_id' => fn (array $attrs) => Committee::withoutGlobalScopes()->find($attrs['committee_id'])?->site_id,
            'annual_plan_id' => null,
            'schedule_id' => fn (array $attrs) => Schedule::withoutGlobalScopes()->firstOrCreate(
                ['site_id' => $attrs['site_id'], 'kind' => Schedule::GENERAL, 'year' => Carbon::parse($attrs['scheduled_at'])->year],
                ['starts_on' => Carbon::parse($attrs['scheduled_at'])->startOfYear(), 'ends_on' => Carbon::parse($attrs['scheduled_at'])->endOfYear()],
            )->id,
            'title' => 'Ensayo general del coro',
            'description' => 'Ensayo del repertorio para el culto del domingo.',
            'scheduled_at' => now()->addWeek(),
            'ends_at' => fn (array $attrs) => Carbon::parse($attrs['scheduled_at'])->addHours(2),
            'location' => 'Templo',
            'activity_type_id' => fn (array $attrs) => ActivityType::withoutGlobalScopes()->where('site_id', $attrs['site_id'])->orderBy('position')->value('id'),
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
