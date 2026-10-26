<?php

/**
 * La aplicacion instalable (T046). Lo que se puede revisar sin un celular: que el manifiesto
 * este completo, que sus iconos midan lo que dice, que las copias de la paleta no se hayan
 * desviado, y que el trabajador de fondo no guarde nada de la congregacion en el telefono.
 */
function manifiestoDeLaApp(): array
{
    return json_decode(file_get_contents(public_path('manifest.webmanifest')), true, flags: JSON_THROW_ON_ERROR);
}

it('el manifiesto tiene lo que el celular exige para ofrecer instalarla', function () {
    $manifiesto = manifiestoDeLaApp();

    expect($manifiesto['name'])->toBe('Comités IPUC')
        ->and($manifiesto['short_name'])->not->toBeEmpty()
        ->and($manifiesto['start_url'])->toBe('/dashboard')
        ->and($manifiesto['display'])->toBe('standalone')
        ->and($manifiesto['lang'])->toBe('es-CO');
});

it('cada icono existe y mide lo que el manifiesto dice', function () {
    $iconos = collect(manifiestoDeLaApp()['icons']);

    foreach ($iconos as $icono) {
        [$ancho, $alto] = getimagesize(public_path(ltrim($icono['src'], '/')));
        expect("{$ancho}x{$alto}")->toBe($icono['sizes'], "{$icono['src']} no mide lo que dice");
    }

    expect($iconos->pluck('sizes'))->toContain('192x192', '512x512')
        ->and($iconos->where('purpose', 'maskable'))->not->toBeEmpty();
});

it('toda pantalla anuncia el manifiesto', function () {
    $this->get('/login')->assertSee('<link rel="manifest" href="/manifest.webmanifest">', false);
});

it('las copias de la paleta coinciden con la original', function () {
    $paleta = file_get_contents(resource_path('css/app.css'));
    $manifiesto = manifiestoDeLaApp();
    preg_match_all('/--([a-z-]+):\s*(#[0-9a-f]{6})/i', file_get_contents(public_path('offline.html')), $copias, PREG_SET_ORDER);

    expect($paleta)->toContain('--color-ipuc: '.$manifiesto['theme_color'])
        ->and($paleta)->toContain('--color-fondo: '.$manifiesto['background_color'])
        ->and($copias)->not->toBeEmpty();

    foreach ($copias as [, $nombre, $valor]) {
        expect($paleta)->toContain("--color-{$nombre}: {$valor}");
    }
});

it('el trabajador de fondo no guarda nada de la congregación en el teléfono', function () {
    $trabajador = file_get_contents(public_path('sw.js'));

    // Solo guarda la pagina sin senal y el logo, al instalarse; nunca lo que responde el servidor.
    expect($trabajador)->not->toContain('cache.put')
        ->and($trabajador)->toContain("cache.addAll([SIN_SENAL, '/img/logo-ipuc.webp'])");
});

it('sin señal muestra una página en español, con el logo y un botón para reintentar', function () {
    $pagina = file_get_contents(public_path('offline.html'));

    expect($pagina)->toContain('lang="es"')
        ->and($pagina)->toContain('No hay conexión a internet')
        ->and($pagina)->toContain('/img/logo-ipuc.webp')
        ->and($pagina)->toContain('Intentar de nuevo');
});
