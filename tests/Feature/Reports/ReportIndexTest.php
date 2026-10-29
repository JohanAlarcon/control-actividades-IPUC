<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/*
 * Los reportes (T102 a T105; HU-11): las cifras de arriba y los seis grupos, con los filtros de la
 * lista. Las cifras y los comités del filtro llegan cada uno en su propia petición, y ninguna pasa
 * de cinco consultas (R-08, la excepción declarada).
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-06-15 10:00', 'America/Bogota'));
    miembrosDeReporte($this->e);

    $this->cifras = function (string $quien, string $consulta = '') {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $cifras = $this->actingAs($this->e[$quien])->get("/reports{$consulta}", pedidoParcial('report', 'Reports/Index'))->assertOk()->json('props.report');
        $this->consultas = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $cifras;
    };
    $this->grupo = fn (array $cifras, string $clave) => array_column(collect($cifras['groups'])->firstWhere('key', $clave)['rows'], 'count', 'label');
});

test('escenarios 1, 2 y 5: el pastor ve las cifras de la congregación y los seis grupos; quien está en dos comités cuenta una vez', function () {
    $cifras = ($this->cifras)('pastor');

    expect($cifras['summary'])->toMatchArray([
        'total' => 5, 'baptized' => 3, 'baptizedPercent' => 60, 'married' => 2, 'single' => 2,
        'averageAge' => 46, 'withoutAge' => 1, 'withoutCommittee' => 1,
    ])
        ->and(array_column($cifras['groups'], 'title'))->toBe(['Estado civil', 'Por comité', 'Por edad', 'Por género', 'Bautizados', 'Bautismos por año'])
        ->and(($this->grupo)($cifras, 'committees'))->toBe(['Comité de Alabanza' => 2, 'Comité de Damas Dorcas' => 2, 'Sub-comité de Sonido' => 1])
        ->and(($this->grupo)($cifras, 'baptismsByYear'))->toBe(['2015' => 1, '2019' => 2])
        ->and(($this->grupo)($cifras, 'ages'))->toBe(['Niños (0 a 12)' => 0, 'Adolescentes (13 a 17)' => 0, 'Jóvenes (18 a 29)' => 1, 'Adultos (30 a 59)' => 2, 'Adultos mayores (60 o más)' => 1])
        ->and($this->consultas)->toBeLessThanOrEqual(5);
});

test('los filtros de la lista filtran las cifras; por comité cuentan también sus sub-comités', function () {
    expect(($this->cifras)('pastor', '?gender=f')['summary']['total'])->toBe(3)
        ->and(($this->cifras)('pastor', "?committee={$this->e['alabanza']->id}")['summary']['total'])->toBe(3)
        ->and(($this->cifras)('pastor', '?baptized=no&marital=single')['summary']['total'])->toBe(2);
});

test('la primera carga trae los filtros y la aclaración; los comités del filtro llegan aparte, en cinco consultas o menos', function () {
    $pagina = $this->actingAs($this->e['pastor'])->get('/reports?gender=f')->assertOk()->viewData('page');

    expect($pagina['props']['filters']['gender'])->toBe('f')
        ->and($pagina['props']['scopeNotice'])->toBeNull()
        ->and($pagina['props'])->not->toHaveKey('report');

    DB::flushQueryLog();
    DB::enableQueryLog();
    $comites = $this->actingAs($this->e['presidente'])->get('/reports', pedidoParcial('committees', 'Reports/Index'))->assertOk()->json('props.committees');
    $consultas = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect(array_column($comites, 'name'))->toEqualCanonicalizing(['Comité de Alabanza', 'Sub-comité de Sonido', 'Sub-comité de Coro'])
        ->and($consultas)->toBeLessThanOrEqual(5);
});
