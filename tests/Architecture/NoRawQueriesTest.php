<?php

/**
 * Puerta de calidad 6: nada de SQL crudo sobre tablas con ambito.
 *
 * Una consulta cruda se salta el filtro del principio I sin que ninguna prueba
 * de aislamiento lo note, porque esas revisan la respuesta y no el SQL.
 */

/** Lo que se busca: entradas por las que una consulta puede escapar del filtro. */
const PATRONES_PROHIBIDOS = [
    'DB::table(',
    'DB::select(',
    'DB::statement(',
    'DB::raw(',
    '->whereRaw(',
    '->selectRaw(',
    '->havingRaw(',
    '->orderByRaw(',
];

function archivosPhpDeLaAplicacion(): array
{
    $archivos = [];
    $iterador = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(app_path()));

    foreach ($iterador as $archivo) {
        if ($archivo->isFile() && $archivo->getExtension() === 'php') {
            $archivos[] = $archivo->getPathname();
        }
    }

    return $archivos;
}

it('no hay SQL crudo en app/', function () {
    $hallazgos = [];

    foreach (archivosPhpDeLaAplicacion() as $archivo) {
        $contenido = file_get_contents($archivo);

        foreach (PATRONES_PROHIBIDOS as $patron) {
            if (str_contains($contenido, $patron)) {
                $hallazgos[] = basename($archivo).' usa '.$patron;
            }
        }
    }

    expect($hallazgos)->toBeEmpty(
        'Hay SQL crudo sobre tablas con ámbito: '.implode(' · ', $hallazgos)
    );
});

it('ningún modelo quita sus filtros globales fuera del resolvedor de sub-árbol', function () {
    $hallazgos = [];

    foreach (archivosPhpDeLaAplicacion() as $archivo) {
        if (str_contains($archivo, 'SubtreeResolver')) {
            continue;
        }

        $contenido = file_get_contents($archivo);

        // Los modelos pueden necesitarlo dentro de sus propias reglas de guarda,
        // pero nunca en un controlador: ahi seria una fuga.
        if (str_contains($archivo, DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR) && str_contains($contenido, 'withoutGlobalScope')) {
            $hallazgos[] = basename($archivo);
        }
    }

    expect($hallazgos)->toBeEmpty(
        'Un controlador está quitando el filtro de alcance: '.implode(' · ', $hallazgos)
    );
});
