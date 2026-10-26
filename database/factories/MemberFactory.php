<?php

namespace Database\Factories;

use App\Models\Committee;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 *
 * Fichas ficticias, como exige la constitucion: nunca datos reales de personas.
 */
class MemberFactory extends Factory
{
    private const NOMBRES = ['Julián Restrepo', 'Marleny Ortiz', 'Fabio Cárdenas', 'Gloria Patiño', 'Óscar Niño', 'Luz Dary Hernández'];

    public function definition(): array
    {
        $nombre = self::NOMBRES[$this->faker->numberBetween(0, count(self::NOMBRES) - 1)];

        return [
            'committee_id' => Committee::factory(),
            'site_id' => fn (array $attrs) => Committee::withoutGlobalScopes()->find($attrs['committee_id'])?->site_id,
            'name' => "{$nombre} {$this->faker->unique()->numberBetween(1, 99999)}",
            'birth_date' => now()->subYears(35)->toDateString(),
            'gender' => 'm',
            'marital_status' => 'single',
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
