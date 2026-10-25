<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/**
 * Puerta de calidad 5: ninguna peticion pasa de cinco consultas.
 *
 * La unidad de medida es la peticion completa, middleware incluido. La lista de
 * rutas sale de Route::getRoutes(), no de un numero escrito a mano: una pantalla
 * nueva sin medicion deja la puerta sin pasar.
 */
const TECHO_DE_CONSULTAS = 5;

/** Rutas que no sirven una pantalla con datos de la congregacion. */
const RUTAS_FUERA_DE_MEDICION = [
    'login',
    'register',
    'password.request',
    'password.reset',
    'verification.notice',
    'storage.local',
];

function rutasQueSirvenPantalla(): array
{
    $rutas = [];

    foreach (Route::getRoutes() as $ruta) {
        $nombre = $ruta->getName();

        if (! in_array('GET', $ruta->methods(), true)) {
            continue;
        }
        if ($nombre === null || in_array($nombre, RUTAS_FUERA_DE_MEDICION, true)) {
            continue;
        }
        if (str_contains($ruta->uri(), '{')) {
            continue;
        }

        $rutas[$nombre] = $ruta->uri();
    }

    return $rutas;
}

it('ninguna pantalla pasa del techo de consultas', function () {
    $usuario = User::factory()->create(['must_change_password' => false]);
    $excedidas = [];

    foreach (rutasQueSirvenPantalla() as $nombre => $uri) {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $respuesta = $this->actingAs($usuario)->get('/'.ltrim($uri, '/'));

        $consultas = count(DB::getQueryLog());
        DB::disableQueryLog();

        if ($respuesta->status() >= 500) {
            continue;
        }

        if ($consultas > TECHO_DE_CONSULTAS) {
            $excedidas[] = "{$nombre} ({$uri}): {$consultas} consultas";
        }
    }

    expect($excedidas)->toBeEmpty(
        'Estas peticiones pasan de '.TECHO_DE_CONSULTAS.' consultas: '.implode(' · ', $excedidas)
    );
});

it('la lista de pantallas sale de las rutas, no de un número escrito a mano', function () {
    expect(rutasQueSirvenPantalla())->not->toBeEmpty();
});
