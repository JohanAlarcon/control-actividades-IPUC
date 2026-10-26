<?php

use App\Models\Member;
use App\Models\Sympathizer;

/*
 * Lo que la ficha de una persona no deja guardar a medias (T090, RF-031, RF-032): una edad suelta,
 * sin el dia en que se anoto, envejeceria sin que nadie lo note.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
});

test('la edad anotada no se guarda sin el día en que se anotó, ni el día sin la edad', function (array $datos) {
    Member::factory()->forCommittee($this->e['sonido'])->create(['birth_date' => null] + $datos);
})->with([
    'edad sin día' => [['age_reported' => 40, 'age_reported_at' => null]],
    'día sin edad' => [['age_reported' => null, 'age_reported_at' => '2026-01-10']],
])->throws(RuntimeException::class, 'sin ese día');

test('en la ficha de un simpatizante, igual', function () {
    $ev = evangelismoDePrueba($this->e);

    Sympathizer::factory()->forCommittee($ev['evangelismo'])->create(['age_reported' => 30, 'age_reported_at' => null]);
})->throws(RuntimeException::class, 'sin ese día');

test('la mayoría de edad confirmada se guarda con su fecha', function () {
    Member::factory()->forCommittee($this->e['sonido'])->create(['birth_date' => null, 'adult_confirmed' => true, 'adult_confirmed_at' => null]);
})->throws(RuntimeException::class, 'con su fecha');
