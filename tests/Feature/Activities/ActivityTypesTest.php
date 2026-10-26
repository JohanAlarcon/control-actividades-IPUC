<?php

use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\Site;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Las clases de actividad las administra el pastor (D-25): agrega, cambia el nombre, desactiva y
 * activa. Ninguna se borra, porque las actividades que ya la tienen son historia.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->clase = fn (string $nombre) => ActivityType::withoutGlobalScopes()->where('site_id', $this->e['sede']->id)->where('name', $nombre)->sole();
});

test('cada congregación empieza con las ocho clases de siempre', function () {
    expect(ActivityType::where('site_id', $this->e['sede']->id)->orderBy('position')->pluck('name')->all())
        ->toBe(['Culto', 'Ensayo', 'Reunión', 'Evento', 'Salida', 'Visita', 'Campaña', 'Convención']);
});

test('el pastor ve las clases y en cuántas actividades se han usado', function () {
    Activity::factory()->forCommittee($this->e['sonido'])->count(2)->create(['activity_type_id' => ($this->clase)('Ensayo')->id]);

    $this->actingAs($this->e['pastor'])->get('/activity-types')
        ->assertInertia(fn (Assert $page) => $page
            ->component('ActivityTypes/Index')
            ->has('types', 8)
            ->where('types.1.name', 'Ensayo')
            ->where('types.1.uses', 2));
});

test('una clase nueva se ofrece enseguida al proponer', function () {
    $this->actingAs($this->e['pastor'])->post('/activity-types', ['name' => '  Ayuno  '])
        ->assertSessionHas('notice', 'Listo. La clase «Ayuno» quedó agregada y ya se ofrece al proponer.');

    $this->actingAs($this->e['liderSonido'])->get('/activities/create')
        ->assertInertia(fn (Assert $page) => $page->has('types', 9)->where('types.8.name', 'Ayuno'));
});

test('una clase desactivada no se ofrece ni se acepta, y las actividades que la tienen no cambian', function () {
    $salida = ($this->clase)('Salida');
    $actividad = Activity::factory()->forCommittee($this->e['sonido'])->create(['activity_type_id' => $salida->id]);

    $this->actingAs($this->e['pastor'])->patch("/activity-types/{$salida->id}", ['active' => false])
        ->assertSessionHas('notice', 'Listo. «Salida» ya no se ofrece al proponer. Las actividades que la tienen no cambian.');

    $this->actingAs($this->e['liderSonido'])->get('/activities/create')
        ->assertInertia(fn (Assert $page) => $page
            ->has('types', 7)
            ->where('types', fn ($clases) => $clases->pluck('name')->doesntContain('Salida')));

    $this->travelTo(Carbon::parse('2026-03-10 10:00', 'America/Bogota'));
    $this->actingAs($this->e['liderSonido'])
        ->post('/activities', ['title' => 'Paseo', 'activity_type_id' => $salida->id, 'date' => '2026-04-18', 'time' => '08:00'])
        ->assertSessionHasErrors(['activity_type_id' => 'Escoja una de las clases de actividad que se ofrecen.']);

    expect($actividad->fresh()->activity_type_id)->toBe($salida->id);
});

test('el nombre es obligatorio y no se repite', function (string $nombre, string $mensaje) {
    $this->actingAs($this->e['pastor'])->post('/activity-types', ['name' => $nombre])
        ->assertSessionHasErrors(['name' => $mensaje]);
})->with([
    'vacío' => ['   ', 'Escriba el nombre de la clase. Por ejemplo: Ayuno.'],
    'repetido' => ['Culto', 'Ya hay una clase con ese nombre. Si está desactivada, actívela de nuevo.'],
]);

test('el pastor le cambia el nombre a una clase', function () {
    $reunion = ($this->clase)('Reunión');

    $this->actingAs($this->e['pastor'])->patch("/activity-types/{$reunion->id}", ['name' => 'Reunión de comité'])
        ->assertSessionHas('notice', 'Listo. Ahora se llama «Reunión de comité».');

    expect($reunion->fresh()->name)->toBe('Reunión de comité');
});

test('nadie más que el pastor las administra', function (string $quien) {
    $culto = ($this->clase)('Culto');

    $this->actingAs($this->e[$quien])->get('/activity-types')->assertForbidden();
    $this->actingAs($this->e[$quien])->post('/activity-types', ['name' => 'Ayuno'])->assertForbidden();
    $this->actingAs($this->e[$quien])->patch("/activity-types/{$culto->id}", ['active' => false])->assertForbidden();

    expect($culto->fresh()->active)->toBeTrue();
})->with(['presidente', 'liderSonido', 'secretaria']);

test('las clases de otra congregación no se ven ni se tocan', function () {
    $otra = Site::factory()->create();
    $ajena = ActivityType::withoutGlobalScopes()->where('site_id', $otra->id)->where('name', 'Culto')->sole();

    $this->actingAs($this->e['pastor'])->get('/activity-types')
        ->assertInertia(fn (Assert $page) => $page->has('types', 8));

    $this->actingAs($this->e['pastor'])->patch("/activity-types/{$ajena->id}", ['name' => 'Otra cosa'])->assertNotFound();

    expect($ajena->fresh()->name)->toBe('Culto');
});
