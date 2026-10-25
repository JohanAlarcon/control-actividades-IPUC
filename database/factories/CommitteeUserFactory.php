<?php

namespace Database\Factories;

use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommitteeUser>
 */
class CommitteeUserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'site_id' => fn (array $attrs) => Committee::find($attrs['committee_id'])?->site_id,
            'user_id' => User::factory(),
            'committee_id' => Committee::factory(),
            'role' => CommitteeUser::PRESIDENT,
            'started_at' => now()->subMonths(6),
            'ended_at' => null,
        ];
    }

    public function role(string $role): static
    {
        return $this->state(fn () => ['role' => $role]);
    }

    public function forCommittee(Committee $committee): static
    {
        return $this->state(fn () => [
            'committee_id' => $committee->id,
            'site_id' => $committee->site_id,
        ]);
    }

    public function ended(): static
    {
        return $this->state(fn () => ['ended_at' => now()->subMonth()]);
    }
}
