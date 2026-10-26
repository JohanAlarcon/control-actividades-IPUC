<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Approval;
use App\Models\Committee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Approval>
 */
class ApprovalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'committee_id' => Committee::factory(),
            'site_id' => fn (array $attrs) => Committee::find($attrs['committee_id'])?->site_id,
            'approvable_type' => Activity::class,
            'approvable_id' => Activity::factory(),
            'type' => 'activity',
            'level' => Approval::LEVEL_PRESIDENT,
            'status' => Approval::PENDING,
            'title' => 'Vigilia de fin de mes',
            'detail' => null,
            'amount' => null,
            'requested_by_user_id' => User::factory(),
            'requested_at' => now()->subDays(4),
        ];
    }

    public function forCommittee(Committee $committee): static
    {
        return $this->state(fn () => [
            'committee_id' => $committee->id,
            'site_id' => $committee->site_id,
        ]);
    }

    public function resolved(string $status = Approval::APPROVED): static
    {
        return $this->state(fn (array $atributos) => [
            'status' => $status,
            'level' => $status === Approval::APPROVED ? Approval::LEVEL_PASTOR : $atributos['level'],
            'resolved_at' => now(),
            'reason' => $status === Approval::REJECTED ? 'No alcanza el presupuesto este mes.' : null,
        ]);
    }
}
