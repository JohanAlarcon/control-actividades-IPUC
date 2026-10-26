<?php

use App\Support\People\PersonAge;
use Illuminate\Support\Carbon;

/*
 * La edad se calcula el dia de la consulta (T091, RF-031): de la fecha de nacimiento, o de la edad
 * anotada mas los años que pasaron desde que se anoto. Sin ninguna de las dos, no hay edad.
 */

beforeEach(function () {
    $this->hoy = Carbon::parse('2026-06-15', 'America/Bogota');
});

test('de la fecha de nacimiento: quien cumplió dieciocho ayer los tiene; quien los cumple mañana, todavía no', function () {
    expect(PersonAge::of(Carbon::parse('2008-06-14'), null, null, $this->hoy))->toBe(18)
        ->and(PersonAge::of(Carbon::parse('2008-06-16'), null, null, $this->hoy))->toBe(17);
});

test('de la edad anotada: cuarenta años anotados hace dos años son cuarenta y dos', function () {
    expect(PersonAge::of(null, 40, Carbon::parse('2024-06-10'), $this->hoy))->toBe(42);
});

test('sin fecha ni edad anotada no hay edad, en vez de reportar cero', function () {
    expect(PersonAge::of(null, null, null, $this->hoy))->toBeNull()
        ->and(PersonAge::of(null, 40, null, $this->hoy))->toBeNull();
});

test('si hay fecha de nacimiento, manda la fecha', function () {
    expect(PersonAge::of(Carbon::parse('1990-01-01'), 10, Carbon::parse('2020-01-01'), $this->hoy))->toBe(36);
});
