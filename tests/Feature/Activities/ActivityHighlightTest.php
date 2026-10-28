<?php

use App\Models\Activity;
use App\Models\ActivityType;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * El tema y la marca de color de cada actividad (T255; RF-116, D-47): se guardan al proponer y al
 * cargar al plan, y el cronograma los trae con lo propio. De lo ajeno no viajan (P-09), y eso lo
 * vigila la lista blanca de SharedCalendarTest.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-12-01 10:00', 'America/Bogota'));
    $this->propuesta = [
        'title' => 'Día misionero',
        'activity_type_id' => ActivityType::where('site_id', $this->e['sede']->id)->where('name', 'Culto')->value('id'),
        'date' => '2027-02-07',
        'time' => '09:45',
        'ends' => '12:00',
        'theme' => 'Id y haced discípulos',
        'highlight' => 'special_day',
    ];
});

test('se guardan al cargar una actividad al plan del año siguiente', function () {
    $this->actingAs($this->e['presidente'])->post('/annual-plan/2027/activities', $this->propuesta)->assertSessionHasNoErrors();

    expect(Activity::sole())->theme->toBe('Id y haced discípulos')->highlight->toBe('special_day');
});

test('el cronograma trae el tema, la marca y la hora de fin de lo propio', function () {
    $this->actingAs($this->e['presidente'])->post('/annual-plan/2027/activities', $this->propuesta);

    $this->actingAs($this->e['liderSonido'])->get('/activities?year=2027')
        ->assertInertia(fn (Assert $page) => $page->where('months.1.activities.0.title', 'Día misionero')->missing('months.1.activities.0.theme')->missing('months.1.activities.0.highlight'));

    $this->actingAs($this->e['presidente'])->get('/activities?year=2027')
        ->assertInertia(fn (Assert $page) => $page
            ->where('months.1.activities.0.theme', 'Id y haced discípulos')
            ->where('months.1.activities.0.highlight', 'special_day')
            ->where('months.1.activities.0.endsAt', Carbon::parse('2027-02-07 12:00', 'America/Bogota')->toIso8601String()));
});

test('las marcas son solo las del cronograma de la sede', function (string $marca) {
    $this->actingAs($this->e['presidente'])->post('/annual-plan/2027/activities', ['highlight' => $marca] + $this->propuesta)->assertSessionHasNoErrors();
})->with(['special_day', 'district', 'national']);
