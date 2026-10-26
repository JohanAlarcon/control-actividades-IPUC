<?php

use App\Support\Sympathizers\CommitmentLevel as Compromiso;
use Inertia\Testing\AssertableInertia as Assert;

/* La sección de simpatizantes (T116; HU-14 escenario 1): cuántos, cómo van y el embudo. */

test('1. dice cuántas personas se acompañan, cuántas crecen, cuántas están estables y cuántas se alejan', function () {
    $e = estructuraDePrueba();
    $ev = evangelismoDePrueba($e);

    simpatizanteDePrueba($ev, ['name' => 'Creciendo'], 3, [2, 5, 9]);
    simpatizanteDePrueba($ev, ['name' => 'Estable'], 2, [Compromiso::GROWING_MAX_DAYS + 5]);
    simpatizanteDePrueba($ev, ['name' => 'Alejándose'], 1, [Compromiso::DRIFTING_DAYS + 30]);

    $this->actingAs($ev['presidenteEvangelismo'])->get('/sympathizers')
        ->assertInertia(fn (Assert $page) => $page
            ->where('stats.total', 3)
            ->where('stats.growing', 1)
            ->where('stats.stable', 1)
            ->where('stats.drifting', 1)
            ->where('funnel.0.count', 3)
            ->where('funnel.1.count', 2)
            ->where('funnel.2.count', 1)
            ->where('funnel.3.count', 0));
});
