<?php

namespace Database\Factories;

use App\Models\Committee;
use App\Models\Schedule;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    public function definition(): array
    {
        $anio = now()->year;

        return [
            'site_id' => Site::factory(),
            'committee_id' => null,
            'kind' => Schedule::GENERAL,
            'year' => $anio,
            'name' => null,
            'starts_on' => "{$anio}-01-01",
            'ends_on' => "{$anio}-12-31",
            'active' => true,
        ];
    }

    /** Un cronograma interno del comité, con su nombre. */
    public function internalOf(Committee $committee, string $name = 'Módulo 1'): static
    {
        return $this->state(fn () => [
            'site_id' => $committee->site_id,
            'committee_id' => $committee->id,
            'kind' => Schedule::INTERNAL,
            'name' => $name,
        ]);
    }
}
