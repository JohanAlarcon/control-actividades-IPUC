<?php

use App\Models\Committee;
use App\Models\Site;
use Illuminate\Validation\ValidationException;

it('guarda un comité en el primer nivel', function () {
    $comite = Committee::factory()->create();

    expect($comite->depth)->toBe(1)
        ->and($comite->isTopLevel())->toBeTrue();
});

it('calcula el nivel de un sub-comité', function () {
    $comite = Committee::factory()->create();
    $subComite = Committee::factory()->childOf($comite)->create();

    expect($subComite->depth)->toBe(2)
        ->and($subComite->parent->id)->toBe($comite->id);
});

it('CL-17: rechaza colgar un grupo de un sub-comité y explica que la estructura llega hasta dos niveles', function () {
    $comite = Committee::factory()->create();
    $subComite = Committee::factory()->childOf($comite)->create();

    expect(fn () => Committee::factory()->childOf($subComite)->create())
        ->toThrow(ValidationException::class, 'La estructura llega hasta dos niveles: comité y sub-comité.');
});

it('reconoce un nodo sin hijos, que es el caso límite CL-01', function () {
    $site = Site::factory()->create();
    $conHijos = Committee::factory()->for($site)->create();
    Committee::factory()->childOf($conHijos)->create();
    $sinHijos = Committee::factory()->for($site)->create();

    expect($conHijos->hasChildren())->toBeTrue()
        ->and($sinHijos->hasChildren())->toBeFalse();
});

it('no permite dos comités con el mismo nombre bajo el mismo padre', function () {
    $comite = Committee::factory()->create(['name' => 'Comité de Alabanza']);

    expect(fn () => Committee::factory()->for($comite->site)->create([
        'name' => 'Comité de Alabanza',
        'parent_id' => null,
    ]))->toThrow(ValidationException::class, 'Ya hay un comité con ese nombre');
});

it('sí permite el mismo nombre en ramas distintas', function () {
    $alabanza = Committee::factory()->create(['name' => 'Comité de Alabanza']);
    $damas = Committee::factory()->for($alabanza->site)->create(['name' => 'Damas Dorcas']);

    Committee::factory()->childOf($alabanza)->create(['name' => 'Logística']);
    $otra = Committee::factory()->childOf($damas)->create(['name' => 'Logística']);

    expect($otra->name)->toBe('Logística');
});

it('nunca deja escribir el nivel a mano', function () {
    $comite = Committee::factory()->create();
    $subComite = Committee::factory()->childOf($comite)->create(['depth' => 99]);

    expect($subComite->depth)->toBe(2);
});
