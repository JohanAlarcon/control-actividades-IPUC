<?php

namespace Database\Factories;

use App\Models\Committee;
use App\Models\CommitteeMember;
use App\Models\Member;
use App\Models\Site;
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
            'site_id' => Site::factory(),
            'name' => "{$nombre} {$this->faker->unique()->numberBetween(1, 99999)}",
            'birth_date' => now()->subYears(35)->toDateString(),
            'gender' => 'm',
            'marital_status' => 'single',
        ];
    }

    /** El miembro, en la lista de ese comité desde hoy, con la función que se diga (D-32). */
    public function forCommittee(Committee $committee, string $role = 'member'): static
    {
        return $this->state(fn () => ['site_id' => $committee->site_id])
            ->afterCreating(fn (Member $miembro) => CommitteeMember::create([
                'site_id' => $committee->site_id,
                'member_id' => $miembro->id,
                'committee_id' => $committee->id,
                'role' => $role,
                'since' => now()->toDateString(),
            ]));
    }

    /** En el padrón de la sede, sin estar todavía en la lista de ningún comité. */
    public function forSite(Site $site): static
    {
        return $this->state(fn () => ['site_id' => $site->id]);
    }
}
