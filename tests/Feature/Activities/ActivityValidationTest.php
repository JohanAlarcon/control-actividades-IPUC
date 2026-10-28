<?php

use App\Models\Activity;
use App\Models\ActivityType;
use Illuminate\Support\Carbon;

/*
 * Las reglas del formulario de propuesta (T063). La fecha va de hoy al 31 de diciembre, en hora de
 * Colombia (CL-07, S-04), y cada error nombra el campo que hay que corregir (HU-05, escenario 4).
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-03-10 10:00', 'America/Bogota'));
    $this->propuesta = [
        'title' => 'Vigilia de jóvenes',
        'activity_type_id' => ActivityType::where('site_id', $this->e['sede']->id)->where('name', 'Culto')->value('id'),
        'date' => '2026-04-18',
        'time' => '19:00',
        'ends' => '21:00',
        'location' => 'Templo central',
        'description' => 'Con alabanza y oración.',
        'budget' => '',
    ];
});

test('cada dato que falta o está mal se señala en su propio campo', function (array $cambio, string $campo, string $mensaje) {
    $this->actingAs($this->e['liderSonido'])->from('/activities/create')
        ->post('/activities', $cambio + $this->propuesta)
        ->assertRedirect('/activities/create')
        ->assertSessionHasErrors([$campo => $mensaje]);

    expect(Activity::count())->toBe(0);
})->with([
    'sin nombre' => [['title' => ''], 'title', 'Escriba el nombre de la actividad.'],
    'sin clase' => [['activity_type_id' => ''], 'activity_type_id', 'Escoja qué clase de actividad es.'],
    'una clase que no existe' => [['activity_type_id' => 999999], 'activity_type_id', 'Escoja una de las clases de actividad que se ofrecen.'],
    'de otro año' => [['date' => '2027-01-15'], 'date', 'La fecha debe ser de este año, 2026. Lo del año que viene va en el plan del próximo año.'],
    'ya pasada (CL-07)' => [['date' => '2026-03-09'], 'date', 'Esa fecha ya pasó. Escoja una de hoy en adelante.'],
    'hoy, a una hora que ya pasó' => [['date' => '2026-03-10', 'time' => '08:00'], 'time', 'Esa hora de hoy ya pasó. Escoja una hora más tarde, u otro día.'],
    'sin hora' => [['time' => ''], 'time', 'Escoja la hora de inicio.'],
    'sin hora de fin' => [['ends' => ''], 'ends', 'Escoja la hora en que termina.'],
    'la hora de fin antes del inicio' => [['ends' => '18:30'], 'ends', 'La hora de fin debe ser después de la hora de inicio.'],
    'la hora de fin igual al inicio' => [['ends' => '19:00'], 'ends', 'La hora de fin debe ser después de la hora de inicio.'],
    'un tema muy largo' => [['theme' => str_repeat('a', 121)], 'theme', 'El tema es muy largo. Resúmalo en menos de 120 letras.'],
    'una marca que no existe' => [['highlight' => 'urgente'], 'highlight', 'Escoja una de las formas de destacarla que se ofrecen.'],
    'un presupuesto con letras' => [['budget' => 'mucho'], 'budget', 'Escriba el presupuesto solo con números. Por ejemplo: 350.000.'],
]);

test('hoy, más tarde, sí se puede proponer', function () {
    $this->actingAs($this->e['liderSonido'])->post('/activities', ['date' => '2026-03-10', 'time' => '19:00', 'ends' => '20:00'] + $this->propuesta)
        ->assertSessionHasNoErrors();

    expect(Activity::count())->toBe(1);
});

test('el presupuesto se escribe como se dice: «$ 350.000» son 350000 pesos', function () {
    $this->actingAs($this->e['liderSonido'])->post('/activities', ['budget' => '$ 350.000'] + $this->propuesta)
        ->assertSessionHasNoErrors();

    expect((float) Activity::sole()->budget_amount)->toBe(350000.0);
});

test('la fecha y la hora se guardan como las escogió la persona, en hora de Colombia', function () {
    $this->actingAs($this->e['liderSonido'])->post('/activities', $this->propuesta);

    expect(Activity::sole())
        ->scheduled_at->setTimezone('America/Bogota')->format('Y-m-d H:i')->toBe('2026-04-18 19:00')
        ->ends_at->setTimezone('America/Bogota')->format('Y-m-d H:i')->toBe('2026-04-18 21:00');
});
