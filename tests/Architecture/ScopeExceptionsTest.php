<?php

/*
 * Las cuatro excepciones del principio I (T220, constitución 3.0.0). Quitar el filtro de alcance
 * solo se permite en los lugares con nombre propio que enumera la constitución, y en las guardas
 * internas de los modelos y del cálculo del propio ámbito. Cada lugar tiene contadas sus veces: un
 * withoutGlobalScope nuevo, en cualquier archivo, pone esta prueba roja hasta que alguien decida
 * expresamente que es una de las cuatro excepciones.
 */

const SALIDAS_PERMITIDAS = [
    // Excepción 1: el cronograma general compartido, Activity::forSharedCalendar().
    'Models/Activity.php' => 1,
    // Excepción 3: lo que se pregunta sin traer datos —conteos y sí o no—.
    'Support/Scope/HiddenCounts.php' => 2,
    'Support/Sympathizers/DuplicateCheck.php' => 1,
    // Excepción 4: el buscador del padrón, Member::forPicker().
    'Models/Member.php' => 1,
    // Excepción 2: BIS. Committee::forAssignment(), la lista para asignar; el filtro de los
    // simpatizantes no quita nada, simplemente no se aplica a un cargo de BIS.
    // No son salidas: las guardas de los modelos (nombre repetido, profundidad, cargo válido), el
    // comité que recibe simpatizantes, y el cálculo del sub-árbol del cargo.
    'Models/Committee.php' => 4,
    'Models/CommitteeUser.php' => 1,
    'Support/Committees/SubtreeResolver.php' => 2,
];

it('solo los lugares con nombre propio quitan el filtro de alcance, y cada uno las veces contadas', function () {
    $encontradas = [];
    $iterador = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(app_path()));

    foreach ($iterador as $archivo) {
        if ($archivo->getExtension() !== 'php') {
            continue;
        }

        $veces = substr_count(file_get_contents($archivo->getPathname()), 'withoutGlobalScope');

        if ($veces > 0) {
            $encontradas[str_replace(DIRECTORY_SEPARATOR, '/', substr($archivo->getPathname(), strlen(app_path()) + 1))] = $veces;
        }
    }

    ksort($encontradas);
    $permitidas = SALIDAS_PERMITIDAS;
    ksort($permitidas);

    expect($encontradas)->toBe($permitidas);
});
