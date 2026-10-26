<?php

use App\Models\Sympathizer;
use Illuminate\Support\Carbon;

/* Registrar a un simpatizante (T110, T111; HU-13 escenarios 1 y 3; CL-09). */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->ev = evangelismoDePrueba($this->e);
    $this->travelTo(Carbon::parse('2026-06-15 10:00', 'America/Bogota'));
    $this->persona = [
        'name' => 'Andrea Milena Castro',
        'age_mode' => 'age',
        'age_reported' => 28,
        'gender' => 'f',
        'phone' => '320 555 3400',
        'neighborhood' => 'Picaleña',
        'arrival_source' => 'invitation',
        'first_contact_at' => '2026-06-14',
    ];
});

test('1. cualquier cargo la registra: queda como quien la invitó, y el seguimiento pasa al Comité de Evangelismo', function () {
    $this->actingAs($this->e['liderSonido'])->post('/sympathizers', $this->persona)
        ->assertRedirect('/dashboard')
        ->assertSessionHas('notice', 'Listo. Andrea Milena Castro quedó registrada, y usted como quien lo invitó. Su seguimiento lo lleva la presidencia del Comité de Evangelismo; por eso usted no verá su ficha.');

    $persona = Sympathizer::withoutGlobalScopes()->sole();

    expect($persona)
        ->committee_id->toBe($this->ev['evangelismo']->id)
        ->invited_by_user_id->toBe($this->e['liderSonido']->id)
        ->stage->toBe(1)
        ->age_reported->toBe(28)
        ->and($persona->stages()->sole())->stage->toBe(1)->reached_at->toDateString()->toBe('2026-06-14');
});

test('quien preside Evangelismo la registra y llega directo a su ficha', function () {
    $this->actingAs($this->ev['presidenteEvangelismo'])->post('/sympathizers', $this->persona)
        ->assertRedirect('/sympathizers/'.Sympathizer::withoutGlobalScopes()->sole()->id);
});

test('3. si ya hay alguien con ese nombre y ese teléfono, avisa y deja continuar o cancelar', function () {
    simpatizanteDePrueba($this->ev, ['name' => 'Andrea Milena Castro', 'phone' => '3205553400']);

    $this->actingAs($this->e['liderCoro'])->from('/sympathizers/create')->post('/sympathizers', $this->persona)
        ->assertRedirect('/sympathizers/create')
        ->assertSessionHasErrors(['duplicate' => 'Ya hay una persona registrada con el nombre «Andrea Milena Castro» y ese teléfono. Si es la misma, no hace falta registrarla otra vez.']);

    expect(Sympathizer::withoutGlobalScopes()->count())->toBe(1);

    $this->actingAs($this->e['liderCoro'])->post('/sympathizers', $this->persona + ['confirm_duplicate' => true])->assertSessionHasNoErrors();

    expect(Sympathizer::withoutGlobalScopes()->count())->toBe(2);
});

test('el mismo nombre con otro teléfono no se toma por la misma persona', function () {
    simpatizanteDePrueba($this->ev, ['name' => 'Andrea Milena Castro', 'phone' => '3001112233']);

    $this->actingAs($this->e['liderCoro'])->post('/sympathizers', $this->persona)->assertSessionHasNoErrors();
});

test('el formulario de registro se le abre a cualquier cargo', function () {
    $this->actingAs($this->e['liderMontaje'])->get('/sympathizers/create')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Sympathizers/Form'));
});
