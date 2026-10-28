<?php

use App\Support\Schedule\SeriesDates;

/*
 * Las fechas de una actividad que se repite (T226; D-38, RF-088): cada semana, cada quince días o
 * cada mes, de la primera fecha a la última, con un tope de sesenta.
 */

$dias = fn (array $fechas) => array_map(fn ($fecha) => $fecha->toDateString(), $fechas);

test('cada semana, de la primera fecha a la última, las dos incluidas', function () use ($dias) {
    expect($dias(SeriesDates::between('2026-04-04', '2026-04-25', 'weekly')))
        ->toBe(['2026-04-04', '2026-04-11', '2026-04-18', '2026-04-25']);
});

test('cada quince días', function () use ($dias) {
    expect($dias(SeriesDates::between('2026-04-04', '2026-05-20', 'biweekly')))
        ->toBe(['2026-04-04', '2026-04-18', '2026-05-02', '2026-05-16']);
});

test('cada mes cae el mismo día; el 31 cae el último día de los meses cortos y después vuelve al 31', function () use ($dias) {
    expect($dias(SeriesDates::between('2026-01-31', '2026-05-31', 'monthly')))
        ->toBe(['2026-01-31', '2026-02-28', '2026-03-31', '2026-04-30', '2026-05-31']);
});

test('sesenta fechas caben; con una de más se detiene y ya se sabe que pasa del tope', function () {
    expect(SeriesDates::between('2026-01-01', '2027-02-18', 'weekly'))->toHaveCount(SeriesDates::MAX)
        ->and(SeriesDates::between('2026-01-01', '2030-12-31', 'weekly'))->toHaveCount(SeriesDates::MAX + 1);
});
