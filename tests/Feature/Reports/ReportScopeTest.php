<?php

use Illuminate\Support\Carbon;

/*
 * HU-11 escenario 4 (T104): el presidente y el líder obtienen los mismos reportes, solo con su gente,
 * y la pantalla aclara que las cifras de toda la congregación las ve únicamente el pastor.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-06-15 10:00', 'America/Bogota'));
    miembrosDeReporte($this->e);

    $this->cifras = fn (string $quien) => $this->actingAs($this->e[$quien])->get('/reports', pedidoParcial('report', 'Reports/Index'))->assertOk()->json('props.report');
    $this->comites = fn (array $cifras) => array_column(collect($cifras['groups'])->firstWhere('key', 'committees')['rows'], 'count', 'label');
});

test('los números del presidente suman su comité y sus sub-comités, no la congregación', function () {
    $cifras = ($this->cifras)('presidente');

    expect($cifras['summary'])->toMatchArray(['total' => 3, 'baptized' => 2, 'withoutCommittee' => 0])
        ->and(($this->comites)($cifras))->toBe(['Comité de Alabanza' => 2, 'Sub-comité de Sonido' => 1]);
});

test('la presidenta de otro comité ve solo a su gente: de quien está en los dos, solo su propio comité', function () {
    $cifras = ($this->cifras)('presidentaDamas');

    expect($cifras['summary']['total'])->toBe(2)
        ->and(($this->comites)($cifras))->toBe(['Comité de Damas Dorcas' => 2]);
});

test('un líder sin gente en su lista ve ceros, no una pantalla rota (CL-12)', function () {
    $cifras = ($this->cifras)('liderCoro');

    expect($cifras['summary'])->toMatchArray(['total' => 0, 'baptizedPercent' => 0, 'averageAge' => null])
        ->and(($this->comites)($cifras))->toBe([]);
});

test('la aclaración sale para todo cargo y no para el pastor', function () {
    foreach (['presidente', 'secretaria', 'tesorero', 'liderSonido'] as $quien) {
        expect($this->actingAs($this->e[$quien])->get('/reports')->assertOk()->viewData('page')['props']['scopeNotice'])
            ->toBe('Estas cifras son solo de la gente de su cargo. Las de toda la congregación las ve únicamente el pastor.');
    }

    expect($this->actingAs($this->e['pastor'])->get('/reports')->viewData('page')['props']['scopeNotice'])->toBeNull();
});
