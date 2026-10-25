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
    /** Nombres reales de la IPUC, para que los datos de prueba se lean como los de la congregación. */
    private const NOMBRES = [
        'Comité de Alabanza',
        'Comité de Evangelismo',
        'Comité de Damas Dorcas',
        'Comité de Jóvenes',
        'Comité de Escuela Dominical',
        'Comité de Misiones',
        'Comité de Red de Familia',
        'Comité de Obra Social',
    ];

    public function definition(): array
    {
        $n = $this->faker->unique()->numberBetween(1, 999999);
        $name = self::NOMBRES[$n % count(self::NOMBRES)].' '.$n;

        return [
            'site_id' => Site::factory(),
            'parent_id' => null,
            'name' => $name,
            'slug' => Str::slug($name),
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
