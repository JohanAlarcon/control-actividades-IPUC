<?php

use App\Models\MonthNote;
use Illuminate\Support\Carbon;

/*
 * Las notas del mes (T257, RF-118): el pastor anota una campaña de todo el mes o un evento con la
 * fecha pendiente, y sale junto al nombre del mes, en la pantalla y en la hoja impresa, para todos.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-03-10 10:00', 'America/Bogota'));
    $this->nota = ['year' => 2026, 'month' => 5, 'text' => 'Operación rescate, Comité de Jóvenes', 'highlight' => 'special_day'];
});

test('el pastor anota la nota del mes, y todos la ven en el cronograma y en la hoja impresa', function () {
    $this->actingAs($this->e['pastor'])->post('/month-notes', $this->nota)
        ->assertSessionHas('notice', 'Listo. La nota quedó en el mes, en la pantalla y en la hoja impresa.');

    foreach (['/activities?year=2026' => 'Activities/Index', '/activities/print?year=2026&month=5' => 'Activities/Print'] as $pantalla => $componente) {
        $notas = $this->actingAs($this->e['liderSonido'])->withHeaders(pedidoParcial('notes', $componente))->get($pantalla)->json('props.notes');

        expect($notas)->toBe([['id' => MonthNote::sole()->id, 'month' => 5, 'text' => 'Operación rescate, Comité de Jóvenes', 'highlight' => 'special_day']]);
    }
});

test('la nota de un mes no sale en otro año', function () {
    $this->actingAs($this->e['pastor'])->post('/month-notes', $this->nota);
    $this->actingAs($this->e['pastor'])->post('/month-notes', ['year' => 2027] + $this->nota);

    $notas = $this->actingAs($this->e['pastor'])->withHeaders(pedidoParcial('notes', 'Activities/Index'))->get('/activities?year=2027')->json('props.notes');

    expect($notas)->toHaveCount(1)->and(MonthNote::count())->toBe(2);
});

test('un presidente no anota ni quita notas del mes', function () {
    $this->actingAs($this->e['presidente'])->post('/month-notes', $this->nota)->assertForbidden();

    $nota = MonthNote::create($this->nota + ['site_id' => $this->e['sede']->id, 'recorded_by_user_id' => $this->e['pastor']->id]);
    $this->actingAs($this->e['presidente'])->delete("/month-notes/{$nota->id}")->assertForbidden();

    expect(MonthNote::count())->toBe(1);
});

test('el pastor la quita cuando deja de valer', function () {
    $nota = MonthNote::create($this->nota + ['site_id' => $this->e['sede']->id, 'recorded_by_user_id' => $this->e['pastor']->id]);

    $this->actingAs($this->e['pastor'])->delete("/month-notes/{$nota->id}")->assertSessionHas('notice', 'Listo. La nota se quitó del mes.');

    expect(MonthNote::count())->toBe(0);
});

test('cada dato mal escrito se explica', function (array $cambio, string $campo, string $mensaje) {
    $this->actingAs($this->e['pastor'])->post('/month-notes', $cambio + $this->nota)->assertSessionHasErrors([$campo => $mensaje]);
})->with([
    'sin texto' => [['text' => ''], 'text', 'Escriba la nota del mes.'],
    'un mes que no existe' => [['month' => 13], 'month', 'Escoja uno de los doce meses.'],
    'otro año' => [['year' => 2024], 'year', 'Las notas son para 2026 o para 2027.'],
    'una marca que no existe' => [['highlight' => 'azul'], 'highlight', 'Escoja una de las formas de destacarla que se ofrecen.'],
]);
