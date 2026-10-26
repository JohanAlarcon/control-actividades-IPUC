<?php

use App\Models\Member;
use Illuminate\Support\Carbon;

/*
 * Las reglas de la ficha de miembro (T093, HU-10 escenarios 2, 3 y 8): cada rechazo explica por
 * que, y nunca se bloquea por un dato que la congregacion no tiene (RF-032, principio IV).
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-06-15 10:00', 'America/Bogota'));
    $this->ficha = [
        'name' => 'Hermano Julián Restrepo',
        'age_mode' => 'date',
        'birth_date' => '1980-04-12',
        'gender' => 'm',
        'marital_status' => 'married',
        'committee_id' => $this->e['sonido']->id,
        'baptized' => false,
    ];
    $this->guardar = fn (array $cambios) => $this->actingAs($this->e['liderSonido'])->from('/members/create')->post('/members', $cambios + $this->ficha);
});

test('2. un menor marcado casado o viudo se rechaza, y el mensaje explica por qué', function (string $estado) {
    ($this->guardar)(['birth_date' => '2012-01-01', 'marital_status' => $estado])
        ->assertSessionHasErrors(['marital_status' => 'Una persona menor de dieciocho años no puede figurar casada, viuda, separada ni en unión libre. Revise la fecha de nacimiento o el estado civil.']);

    expect(Member::count())->toBe(0);
})->with(['married', 'widowed']);

test('con la edad anotada en vez de la fecha, la regla también se aplica', function () {
    ($this->guardar)(['age_mode' => 'age', 'age_reported' => 15, 'birth_date' => ''])->assertSessionHasErrors('marital_status');
});

test('8. sin fecha ni edad, casado: se pide confirmar que es mayor de edad, y con eso se guarda, por completar', function () {
    ($this->guardar)(['birth_date' => ''])->assertSessionHasErrors([
        'adult_confirmed' => 'Confirme que la persona es mayor de dieciocho años. No hace falta la fecha: basta con que usted lo confirme.',
    ]);

    ($this->guardar)(['birth_date' => '', 'adult_confirmed' => true])->assertSessionHasNoErrors();

    expect(Member::sole())
        ->adult_confirmed->toBeTrue()
        ->adult_confirmed_at->not->toBeNull()
        ->profile_pending->toBeTrue();
});

test('3. bautizado sin la fecha del bautismo no se guarda', function () {
    ($this->guardar)(['baptized' => true])->assertSessionHasErrors(['baptized_at' => 'Anote la fecha del bautismo en el nombre de Jesucristo.']);
});

test('la fecha del bautismo no puede ser anterior al nacimiento', function () {
    ($this->guardar)(['baptized' => true, 'baptized_at' => '1975-01-01'])
        ->assertSessionHasErrors(['baptized_at' => 'La fecha de bautismo no puede ser anterior a la de nacimiento.']);
});

test('un líder no registra a nadie en un comité que no es suyo', function () {
    ($this->guardar)(['committee_id' => $this->e['damas']->id])->assertSessionHasErrors(['committee_id' => 'Escoja uno de los comités de la lista.']);
});
