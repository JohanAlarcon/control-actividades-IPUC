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
        $n = $this->faker->unique()->numberBetween(1, 999999);

        return [
            'name' => 'IPUC Central '.$n,
            'city' => 'Ibagué',
            'active' => true,
        ];
    }
}
