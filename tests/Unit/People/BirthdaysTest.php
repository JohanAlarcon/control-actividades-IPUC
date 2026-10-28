<?php

use App\Support\People\Birthdays;
use Illuminate\Support\Carbon;

/*
 * Los cumpleaños se calculan, no se guardan (D-42): los de hoy y los siete días que siguen, para la
 * alerta del inicio, y los de un mes, para la lista (HU-29).
 */

function conCumpleanos(string $nombre, ?string $nacio): array
{
    return ['kind' => 'member', 'id' => crc32($nombre), 'name' => $nombre, 'code' => 'M-0001', 'birthDate' => $nacio ? Carbon::parse($nacio) : null];
}

$hoy = fn (string $fecha) => Carbon::parse($fecha, 'America/Bogota');
$fechas = fn (array $tarjetas) => array_map(fn (array $tarjeta) => [$tarjeta['name'], $tarjeta['date'], $tarjeta['turns']], $tarjetas);

test('hoy y los siete días que siguen, en orden y con los años que cumplen', function () use ($hoy, $fechas) {
    $personas = collect([
        conCumpleanos('A los siete días', '1990-11-14'),
        conCumpleanos('Hoy', '1981-11-07'),
        conCumpleanos('A los ocho días', '1990-11-15'),
        conCumpleanos('Ayer', '1970-11-06'),
        conCumpleanos('Sin fecha', null),
    ]);

    expect($fechas(Birthdays::soon($personas, $hoy('2026-11-07 18:30'))))->toBe([
        ['Hoy', '2026-11-07', 45],
        ['A los siete días', '2026-11-14', 36],
    ]);
});

test('al final del año, los primeros días de enero ya son del año siguiente', function () use ($hoy, $fechas) {
    expect($fechas(Birthdays::soon(collect([conCumpleanos('Enero', '2000-01-02')]), $hoy('2026-12-28'))))->toBe([['Enero', '2027-01-02', 27]]);
});

test('CL-32: quien nació un 29 de febrero, en un año que no es bisiesto, cumple el 28', function () use ($hoy, $fechas) {
    $bisiesta = collect([conCumpleanos('Bisiesta', '2004-02-29')]);

    expect($fechas(Birthdays::soon($bisiesta, $hoy('2027-02-25'))))->toBe([['Bisiesta', '2027-02-28', 23]])
        ->and($fechas(Birthdays::soon($bisiesta, $hoy('2028-02-25'))))->toBe([['Bisiesta', '2028-02-29', 24]])
        ->and($fechas(Birthdays::ofMonth($bisiesta, 2, 2027)))->toBe([['Bisiesta', '2027-02-28', 23]]);
});

test('por mes, los de ese mes con la edad de ese año; una fecha de nacimiento futura no es un cumpleaños', function () use ($fechas) {
    $personas = collect([
        conCumpleanos('El 20', '1975-11-20'),
        conCumpleanos('El 3', '1999-11-03'),
        conCumpleanos('De octubre', '1980-10-03'),
        conCumpleanos('Mal escrita', '2030-11-10'),
        conCumpleanos('Sin fecha', null),
    ]);

    expect($fechas(Birthdays::ofMonth($personas, 11, 2026)))->toBe([
        ['El 3', '2026-11-03', 27],
        ['El 20', '2026-11-20', 51],
    ]);
});
