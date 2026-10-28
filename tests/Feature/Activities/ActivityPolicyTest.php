<?php

use App\Models\Activity;
use App\Models\User;
use App\Support\Scope\CurrentScope;
use Illuminate\Support\Facades\Gate;

/*
 * Quien propone, quien ve y quien modifica una actividad (T062). Proponer se prueba por la ruta;
 * ver y modificar, con la policy, porque esas pantallas llegan con el cronograma (HU-09).
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->puede = function (User $quien, string $accion, Activity $actividad): bool {
        app(CurrentScope::class)->resolve($quien);

        return Gate::forUser($quien)->allows($accion, $actividad);
    };
});

test('proponen el líder y el presidente, y el formulario dice a quién le va a llegar', function () {
    $this->actingAs($this->e['liderCoro'])->get('/activities/create')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Activities/Form')->where('recipient', 'Hermano Rigoberto Páez'));

    $this->actingAs($this->e['presidente'])->get('/activities/create')
        ->assertInertia(fn ($page) => $page->where('recipient', $this->e['pastor']->name));
});

test('el pastor, la secretaria y el tesorero no proponen: la pantalla no se les abre', function (string $quien) {
    $this->actingAs($this->e[$quien])->get('/activities/create')->assertForbidden();
    $this->actingAs($this->e[$quien])->post('/activities', ['title' => 'Vigilia'])->assertForbidden();

    expect(Activity::count())->toBe(0);
})->with(['pastor', 'secretaria', 'tesorero']);

test('cualquier cargo ve una actividad, también la de otro comité (RF-010)', function () {
    $actividad = Activity::factory()->forCommittee($this->e['sonido'])->create();

    expect(($this->puede)($this->e['presidentaDamas'], 'view', $actividad))->toBeTrue()
        ->and(($this->puede)($this->e['liderCoro'], 'view', $actividad))->toBeTrue();
});

test('solo la modifica el comité dueño, o uno que lo contiene', function () {
    $actividad = Activity::factory()->forCommittee($this->e['sonido'])->create();

    expect(($this->puede)($this->e['liderSonido'], 'update', $actividad))->toBeTrue()
        ->and(($this->puede)($this->e['presidente'], 'update', $actividad))->toBeTrue()
        ->and(($this->puede)($this->e['liderCoro'], 'update', $actividad))->toBeFalse()
        ->and(($this->puede)($this->e['presidentaDamas'], 'update', $actividad))->toBeFalse()
        ->and(($this->puede)($this->e['pastor'], 'update', $actividad))->toBeFalse();
});
