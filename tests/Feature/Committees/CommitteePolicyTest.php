<?php

use App\Support\Scope\CurrentScope;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->e = estructuraDePrueba();
});

function puedeVerElComite($persona, $comite): bool
{
    app(CurrentScope::class)->resolve($persona);

    return Gate::forUser($persona)->allows('view', $comite);
}

it('un presidente ve su comité y todo lo que cuelga de él', function () {
    foreach (['alabanza', 'sonido', 'coro'] as $comite) {
        expect(puedeVerElComite($this->e['presidente'], $this->e[$comite]))->toBeTrue("no ve {$comite}");
    }
});

it('un presidente no ve un comité ajeno', function () {
    expect(puedeVerElComite($this->e['presidente'], $this->e['damas']))->toBeFalse();
});

it('un líder ve su sub-comité, pero no el comité de arriba ni el sub-comité hermano', function () {
    $lider = $this->e['liderSonido'];

    expect(puedeVerElComite($lider, $this->e['sonido']))->toBeTrue()
        ->and(puedeVerElComite($lider, $this->e['alabanza']))->toBeFalse()
        ->and(puedeVerElComite($lider, $this->e['coro']))->toBeFalse();
});

it('el pastor ve todos los comités de su sede', function () {
    foreach (['alabanza', 'sonido', 'coro', 'damas'] as $comite) {
        expect(puedeVerElComite($this->e['pastor'], $this->e[$comite]))->toBeTrue("no ve {$comite}");
    }
});

it('el permiso se pregunta con el ámbito de quien consulta, no con el de otro', function () {
    app(CurrentScope::class)->resolve($this->e['presidente']);

    // El ámbito resuelto es el del presidente de Alabanza: no le sirve a la presidenta de Damas.
    expect(Gate::forUser($this->e['presidentaDamas'])->allows('view', $this->e['damas']))->toBeFalse();
});
