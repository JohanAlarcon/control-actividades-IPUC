<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Site>
 */
class SiteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'IPUC '.$this->faker->unique()->city(),
            'city' => $this->faker->city(),
            'active' => true,
        ];
    }
}
