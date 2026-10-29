<?php

use App\Models\CommitteeMember;
use App\Models\Member;
use App\Support\Reports\MemberStats;
use Illuminate\Support\Carbon;

/*
 * Las cifras de los reportes (T100, T101; RF-036): un grupo vacío da ceros y no una pantalla rota
 * (CL-12), la edad sale de sus dos fuentes y un miembro de dos comités cuenta una vez (escenario 5).
 */

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-06-15 10:00', 'America/Bogota'));
});

function miembroDelReporte(int $id, array $datos, array $comites = []): Member
{
    $miembro = Member::factory()->make($datos + ['birth_date' => null, 'marital_status' => 'single', 'gender' => 'm', 'baptized' => false]);
    $miembro->id = $id;

    return $miembro->setRelation('memberships', collect($comites)->map(fn (string $nombre, int $comite) => (new CommitteeMember)->forceFill([
        'member_id' => $id, 'committee_id' => $comite, 'committee_name' => $nombre,
    ]))->values());
}

$filas = fn (array $cifras, string $grupo) => collect($cifras['groups'])->firstWhere('key', $grupo)['rows'];

test('con cero miembros todo es cero, y la edad promedio no divide por cero', function () use ($filas) {
    $cifras = MemberStats::of(collect());

    expect($cifras['summary'])->toMatchArray(['total' => 0, 'baptized' => 0, 'baptizedPercent' => 0, 'averageAge' => null, 'withoutAge' => 0, 'withoutCommittee' => 0])
        ->and(array_column($filas($cifras, 'marital'), 'count'))->toBe([0, 0, 0, 0, 0, 0])
        ->and(array_column($filas($cifras, 'ages'), 'count'))->toBe([0, 0, 0, 0, 0])
        ->and($filas($cifras, 'committees'))->toBe([])
        ->and($filas($cifras, 'baptismsByYear'))->toBe([]);
});

test('la edad sale de la fecha o de la edad anotada; sin ninguna queda fuera de los cortes y se cuenta aparte', function () use ($filas) {
    $cifras = MemberStats::of(collect([
        miembroDelReporte(1, ['birth_date' => '2000-06-14']),
        miembroDelReporte(2, ['age_reported' => 40, 'age_reported_at' => '2024-06-10']),
        miembroDelReporte(3, []),
    ]));

    expect($cifras['summary']['averageAge'])->toBe(34)
        ->and($cifras['summary']['withoutAge'])->toBe(1)
        ->and(array_column($filas($cifras, 'ages'), 'count', 'label'))->toBe([
            'Niños (0 a 12)' => 0, 'Adolescentes (13 a 17)' => 0, 'Jóvenes (18 a 29)' => 1, 'Adultos (30 a 59)' => 1, 'Adultos mayores (60 o más)' => 0,
        ]);
});

test('escenario 5: quien está en dos comités cuenta una vez en el total y aparece en cada comité', function () use ($filas) {
    $cifras = MemberStats::of(collect([
        miembroDelReporte(1, [], [1 => 'Comité de Alabanza', 2 => 'Comité de Damas Dorcas']),
        miembroDelReporte(2, [], [1 => 'Comité de Alabanza']),
        miembroDelReporte(3, []),
    ]));

    expect($cifras['summary']['total'])->toBe(3)
        ->and($cifras['summary']['withoutCommittee'])->toBe(1)
        ->and($filas($cifras, 'committees'))->toBe([['label' => 'Comité de Alabanza', 'count' => 2], ['label' => 'Comité de Damas Dorcas', 'count' => 1]]);
});

test('los bautizados con su porcentaje, el estado civil, el género y los bautismos por año', function () use ($filas) {
    $cifras = MemberStats::of(collect([
        miembroDelReporte(1, ['baptized' => true, 'baptized_at' => '2021-03-07', 'marital_status' => 'married', 'gender' => 'f']),
        miembroDelReporte(2, ['baptized' => true, 'baptized_at' => '2019-08-11', 'marital_status' => 'married', 'gender' => 'f']),
        miembroDelReporte(3, []),
    ]));

    expect($cifras['summary'])->toMatchArray(['baptized' => 2, 'baptizedPercent' => 67, 'married' => 2, 'single' => 1])
        ->and(array_column($filas($cifras, 'gender'), 'count', 'label'))->toBe(['Mujeres' => 2, 'Hombres' => 1])
        ->and(array_column($filas($cifras, 'baptized'), 'count', 'label'))->toBe(['Bautizados' => 2, 'Sin bautizar' => 1])
        ->and($filas($cifras, 'baptismsByYear'))->toBe([['label' => '2019', 'count' => 1], ['label' => '2021', 'count' => 1]]);
});
