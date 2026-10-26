<?php

/**
 * Los cuatro escenarios de HU-03, por los tres cargos, mas CL-01 (T043).
 */
beforeEach(function () {
    $this->e = estructuraDePrueba();
});

it('escenario 1: el pastor ve la estructura completa de los tres niveles, con sus responsables', function () {
    $this->actingAs($this->e['pastor'])->get('/committees')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('tree', function ($arbol) {
                $arbol = json_decode(json_encode($arbol), true);

                return nodoDelArbol($arbol, 'Comité de Damas Dorcas')['responsible'] === 'Hermana Blanca Cárdenas'
                    && nodoDelArbol($arbol, 'Equipo de Montaje')['responsible'] === 'Hermano Wilson Garzón';
            })
            ->where('reviewer', null)
        );
});

it('escenario 2: un presidente ve su comité y sus sub-comités, y de los demás nada', function () {
    $respuesta = $this->actingAs($this->e['presidente'])->get('/committees');

    $respuesta->assertOk()
        ->assertShows('Equipo de Montaje')
        ->assertShows('Sub-comité de Coro')
        ->assertDoesntShow('Comité de Damas Dorcas')
        ->assertDoesntShow('Blanca Cárdenas');
});

it('escenario 2: los demás comités aparecen solo como un número, que llega aparte', function () {
    $props = $this->actingAs($this->e['presidente'])
        ->withHeaders(pedidoParcial('scopeHidden', 'Committees/Index'))
        ->get('/committees')
        ->assertOk()
        ->json('props');

    expect($props['scopeHidden']['committees'])->toBe(1)
        ->and(json_encode($props))->not->toContain('Damas');
});

it('escenario 3 y CL-01: un comité sin sub-comités lo dice expresamente', function () {
    $this->actingAs($this->e['presidentaDamas'])->get("/committees/{$this->e['damas']->id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('hasChildren', false)->where('responsible', 'Hermana Blanca Cárdenas'));
});

it('escenario 4: un líder ve su sub-comité y el nombre de su presidente, y nada más', function () {
    $respuesta = $this->actingAs($this->e['liderSonido'])->get('/committees');

    $respuesta->assertOk()
        ->assertInertia(fn ($page) => $page->where('reviewer', 'Hermano Rigoberto Páez'))
        ->assertShows('Sub-comité de Sonido')
        ->assertShows('Equipo de Montaje')
        ->assertDoesntShow('Comité de Alabanza')
        ->assertDoesntShow('Sub-comité de Coro')
        ->assertDoesntShow('Comité de Damas Dorcas')
        ->assertDoesntShow('Luz Mary Quintero');
});

it('escenario 4, un nivel más abajo: el líder de un equipo también sabe quién revisa lo que propone', function () {
    $this->actingAs($this->e['liderMontaje'])->get('/committees')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('reviewer', 'Hermano Rigoberto Páez')
            ->count('tree', 1)
            ->where('tree.0.name', 'Equipo de Montaje')
        )
        ->assertDoesntShow('Sub-comité de Sonido');
});

it('escenario 1: cuántos miembros tiene cada nodo')->todo();
