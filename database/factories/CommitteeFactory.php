<?php

namespace Database\Factories;

use App\Models\Committee;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Committee>
 */
class CommitteeFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Comité de '.$this->faker->unique()->word();

        return [
            'site_id' => Site::factory(),
            'parent_id' => null,
            'name' => $name,
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(1, 99999),
            'position' => 0,
            'active' => true,
        ];
    }

    public function childOf(Committee $parent): static
    {
        return $this->state(fn () => [
            'site_id' => $parent->site_id,
            'parent_id' => $parent->id,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['active' => false]);
    }
}
