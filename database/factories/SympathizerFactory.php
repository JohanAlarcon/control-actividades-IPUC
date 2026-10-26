<?php

namespace Database\Factories;

use App\Models\Committee;
use App\Models\Sympathizer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sympathizer>
 *
 * Fichas ficticias, como exige la constitucion: nunca datos reales de personas.
 */
class SympathizerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'committee_id' => Committee::factory(),
            'site_id' => fn (array $attrs) => Committee::withoutGlobalScopes()->find($attrs['committee_id'])?->site_id,
            'name' => 'Simpatizante '.$this->faker->unique()->numberBetween(1, 99999),
            'gender' => 'f',
            'arrival_source' => 'invitation',
            'first_contact_at' => now()->subDays(10)->toDateString(),
            'stage' => 1,
        ];
    }

    public function forCommittee(Committee $committee): static
    {
        return $this->state(fn () => [
            'committee_id' => $committee->id,
            'site_id' => $committee->site_id,
        ]);
    }
}
