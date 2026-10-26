<?php

use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->e = estructuraDePrueba();
});

function arbolDe($prueba, User $persona): array
{
    return $prueba->actingAs($persona)->get('/committees')->assertOk()->viewData('page')['props']['tree'];
}

it('el árbol trae cada comité con sus hijos adentro y el responsable de cada uno', function () {
    $arbol = arbolDe($this, $this->e['pastor']);

    $alabanza = nodoDelArbol($arbol, 'Comité de Alabanza');
    $sonido = nodoDelArbol($alabanza['children'], 'Sub-comité de Sonido');

    expect(collect($alabanza['children'])->pluck('name'))->toContain('Sub-comité de Sonido', 'Sub-comité de Coro')
        ->and($sonido['children'][0]['name'])->toBe('Equipo de Montaje')
        ->and($alabanza['responsible'])->toBe('Hermano Rigoberto Páez')
        ->and($sonido['responsible'])->toBe('Hermano Andrés Molina')
        ->and($sonido['children'][0]['responsible'])->toBe('Hermano Wilson Garzón');
});

it('un comité sin responsable llega sin nombre, para que la pantalla lo diga', function () {
    Committee::factory()->for($this->e['sede'])->create(['name' => 'Comité de Jóvenes']);

    expect(nodoDelArbol(arbolDe($this, $this->e['pastor']), 'Comité de Jóvenes')['responsible'])->toBeNull();
});

it('un comité inactivo no aparece en la estructura', function () {
    Committee::factory()->for($this->e['sede'])->inactive()->create(['name' => 'Comité Antiguo']);

    expect(nodoDelArbol(arbolDe($this, $this->e['pastor']), 'Comité Antiguo'))->toBeNull();
});

it('quien tiene el acceso retirado no aparece como responsable', function () {
    $this->e['liderCoro']->update(['active' => false]);

    expect(nodoDelArbol(arbolDe($this, $this->e['pastor']), 'Sub-comité de Coro')['responsible'])->toBeNull();
});

it('el árbol de un líder de equipo cabe en el presupuesto y no crece con los datos', function () {
    $medir = function () {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($this->e['liderMontaje'])->get('/committees')->assertOk();
        $consultas = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $consultas;
    };

    $antes = $medir();

    foreach (range(1, 6) as $vuelta) {
        $comite = Committee::factory()->for($this->e['sede'])->create();
        $persona = User::factory()->create(['site_id' => $this->e['sede']->id]);
        CommitteeUser::factory()->forCommittee($comite)->create(['user_id' => $persona->id]);
    }

    expect($antes)->toBeLessThanOrEqual(5)
        ->and($medir())->toBe($antes);
});
