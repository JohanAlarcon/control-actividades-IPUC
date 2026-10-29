<?php

use App\Exports\ReportExport;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

/*
 * Descargar e imprimir los reportes (T106 a T108; HU-12, RF-038): el archivo y la hoja impresa traen
 * lo que la persona está viendo, con sus filtros y su ámbito, y nada más.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-06-15 10:00', 'America/Bogota'));
    miembrosDeReporte($this->e);

    $this->archivo = 'reporte-de-miembros-2026-06-15.xlsx';
    $this->filasDelArchivo = function (string $quien, string $consulta = '') {
        Excel::fake();
        $this->actingAs($this->e[$quien])->get("/reports/download{$consulta}")->assertOk();
        $filas = null;
        Excel::assertDownloaded($this->archivo, function (ReportExport $archivo) use (&$filas) {
            $filas = $archivo->sheets()[1]->array();

            return true;
        });

        return array_column($filas, 1);
    };
});

test('escenario 1: con un filtro, el archivo trae solo a quienes lo cumplen', function () {
    expect(($this->filasDelArchivo)('pastor'))->toHaveCount(5)
        ->and(($this->filasDelArchivo)('pastor', '?gender=f'))->toBe(['Gloria Pineda', 'Nubia Aguirre', 'Rocío Vanegas']);
});

test('escenario 3: el archivo de un líder trae únicamente su ámbito', function () {
    expect(($this->filasDelArchivo)('liderSonido'))->toBe(['Fabio Rincón'])
        ->and(($this->filasDelArchivo)('presidente'))->toBe(['Fabio Rincón', 'Gloria Pineda', 'Rocío Vanegas']);
});

test('el archivo se arma de verdad: las cifras con la sede y los filtros, y los miembros sin teléfono ni barrio', function () {
    DB::flushQueryLog();
    DB::enableQueryLog();
    $respuesta = $this->actingAs($this->e['pastor'])->get("/reports/download?committee={$this->e['damas']->id}")->assertOk();
    $consultas = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($respuesta->headers->get('content-disposition'))->toContain($this->archivo);

    $libro = IOFactory::load($respuesta->getFile()->getPathname());
    $cifras = $libro->getSheetByName('Cifras');
    $miembros = $libro->getSheetByName('Miembros');

    expect($cifras->getCell('A1')->getValue())->toBe($this->e['sede']->name)
        ->and($cifras->getCell('A2')->getValue())->toBe('Reporte de miembros del 15 de junio de 2026')
        ->and($cifras->getCell('A3')->getValue())->toBe('Filtros: Comité de Damas Dorcas')
        ->and($miembros->rangeToArray('A1:H1')[0])->toBe(['Código', 'Nombre', 'Edad', 'Género', 'Estado civil', 'Bautizado', 'Año de bautismo', 'Comités'])
        ->and($miembros->getHighestRow())->toBe(3)
        ->and($consultas)->toBeLessThanOrEqual(5);
});

test('escenario 2: la hoja impresa lleva los filtros en palabras y la aclaración del ámbito', function () {
    $pagina = $this->actingAs($this->e['presidente'])->get("/reports/print?committee={$this->e['alabanza']->id}&gender=f")->assertOk()->viewData('page');

    expect($pagina['component'])->toBe('Reports/Print')
        ->and($pagina['props']['filterText'])->toBe('Comité de Alabanza · Mujeres')
        ->and($pagina['props']['scopeNotice'])->not->toBeNull()
        ->and($pagina['props']['report']['summary']['total'])->toBe(2);
});

test('un comité ajeno en el filtro no trae a nadie, ni en la hoja ni en el archivo', function () {
    $pagina = $this->actingAs($this->e['presidente'])->get("/reports/print?committee={$this->e['damas']->id}")->assertOk()->viewData('page');

    expect($pagina['props']['report']['summary']['total'])->toBe(0)
        ->and(($this->filasDelArchivo)('presidente', "?committee={$this->e['damas']->id}"))->toBe([]);
});
