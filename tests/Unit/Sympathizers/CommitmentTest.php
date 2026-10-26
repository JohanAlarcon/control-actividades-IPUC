<?php

use App\Models\Sympathizer;
use App\Support\Sympathizers\CommitmentLevel as Compromiso;
use Illuminate\Support\Carbon;

/*
 * El nivel de compromiso (T115, RF-043, R-07). Los casos se arman con las constantes, asi que
 * cambiar un umbral cuando el Comite de Evangelismo lo decida no obliga a reescribir la prueba.
 */

beforeEach(function () {
    $this->hoy = Carbon::parse('2026-06-15', 'America/Bogota');
    $this->nivel = fn (int $contactos, int $diasSinContacto) => Compromiso::of($contactos, $this->hoy->copy()->subDays($diasSinContacto), $this->hoy)['level'];
});

test('creciendo: suficientes contactos recientes y poco tiempo sin contacto', function () {
    expect(($this->nivel)(Compromiso::GROWING_MIN_CONTACTS, Compromiso::GROWING_MAX_DAYS - 1))->toBe(Compromiso::GROWING);
});

test('estable: al menos un contacto reciente, sin pasar del límite de días', function () {
    expect(($this->nivel)(1, Compromiso::DRIFTING_DAYS - 1))->toBe(Compromiso::STABLE)
        ->and(($this->nivel)(Compromiso::GROWING_MIN_CONTACTS, Compromiso::GROWING_MAX_DAYS))->toBe(Compromiso::STABLE);
});

test('alejándose: demasiados días sin contacto, o ningún contacto reciente', function () {
    expect(($this->nivel)(Compromiso::GROWING_MIN_CONTACTS + 2, Compromiso::DRIFTING_DAYS))->toBe(Compromiso::DRIFTING)
        ->and(($this->nivel)(0, 1))->toBe(Compromiso::DRIFTING);
});

test('dice con qué lo calculó: los contactos recientes y los días desde el último', function () {
    expect(Compromiso::of(2, $this->hoy->copy()->subDays(9), $this->hoy))->toBe(['level' => Compromiso::STABLE, 'recentContacts' => 2, 'daysSinceContact' => 9]);
});

test('el que vuelve meses después conserva sus etapas y sus fechas, y el compromiso se recalcula (CL-10)', function () {
    $e = estructuraDePrueba();
    $ev = evangelismoDePrueba($e);
    $persona = simpatizanteDePrueba($ev, [], 3, [Compromiso::DRIFTING_DAYS + 40]);
    $fechas = $persona->stages()->orderBy('stage')->pluck('reached_at')->all();

    expect(Compromiso::forSympathizer(Sympathizer::withCommitment()->find($persona->id))['level'])->toBe(Compromiso::DRIFTING);

    $persona->visits()->create(['site_id' => $persona->site_id, 'visited_at' => now()->toDateString(), 'type' => 'service', 'by_user_id' => $ev['presidenteEvangelismo']->id]);

    expect(Compromiso::forSympathizer(Sympathizer::withCommitment()->find($persona->id))['level'])->toBe(Compromiso::STABLE)
        ->and($persona->stages()->orderBy('stage')->pluck('reached_at')->all())->toEqual($fechas)
        ->and($persona->fresh()->stage)->toBe(3);
});
