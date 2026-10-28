<?php

use App\Models\CommitteeUser;
use App\Support\Users\RoleUsername;

/*
 * El usuario de la cuenta de un cargo (RF-123): el cargo y la palabra que distingue al comité.
 */

test('el cargo y la palabra que distingue al comité, sin «Comité de»', function () {
    expect(RoleUsername::for(CommitteeUser::PRESIDENT, 'Comité de Alabanza'))->toBe('presidente.alabanza')
        ->and(RoleUsername::for(CommitteeUser::SECRETARY, 'Comité de Damas Dorcas'))->toBe('secretario.damas')
        ->and(RoleUsername::for(CommitteeUser::TREASURER, 'Comité de Escuela Dominical'))->toBe('tesorero.escuela')
        ->and(RoleUsername::for(CommitteeUser::LEADER, 'Sub-comité Hijas del Rey'))->toBe('lider.hijas');
});

test('una palabra corta se junta con la que sigue, y el número del final se conserva', function () {
    expect(RoleUsername::for(CommitteeUser::PRESIDENT, 'Comité de Obra Social'))->toBe('presidente.obrasocial')
        ->and(RoleUsername::for(CommitteeUser::PRESIDENT, 'Comité de Red de Familia'))->toBe('presidente.redfamilia')
        ->and(RoleUsername::for(CommitteeUser::PRESIDENT, 'Comité de Misiones y Evangelismo 2'))->toBe('presidente.misiones2')
        ->and(RoleUsername::for(CommitteeUser::PRESIDENT, 'Comité BIS'))->toBe('presidente.bis');
});

test('sin tildes, mayúsculas ni signos, para escribirlo en cualquier celular', function () {
    expect(RoleUsername::for(CommitteeUser::LEADER, 'Sub-comité Jóvenes de Fe'))->toBe('lider.jovenes')
        ->and(RoleUsername::for(CommitteeUser::PRESIDENT, 'Comité Decom'))->toBe('presidente.decom');
});
