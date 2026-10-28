<?php

use App\Models\Sympathizer;
use App\Models\SympathizerAssignment;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

/* Registrar a un simpatizante (T110, T111, T216; HU-13 escenarios 1, 3 y 4; D-34; CL-09). */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->ev = evangelismoDePrueba($this->e);
    $this->bis = bisDePrueba($this->e);
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

test('1. BIS la registra y escoge el comité que la acompañará: queda asignada a ese único comité', function () {
    $this->actingAs($this->bis['presidentaBis'])->post('/sympathizers', $this->persona + ['committee_id' => $this->ev['evangelismo']->id])
        ->assertRedirect('/sympathizers/'.Sympathizer::withoutGlobalScopes()->sole()->id)
        ->assertSessionHas('notice', 'Listo. Andrea Milena Castro quedó registrada y la acompaña Comité de Evangelismo.');

    $persona = Sympathizer::withoutGlobalScopes()->sole();

    expect($persona)
        ->committee_id->toBe($this->ev['evangelismo']->id)
        ->invited_by_user_id->toBe($this->bis['presidentaBis']->id)
        ->stage->toBe(1)
        ->age_reported->toBe(28)
        ->and($persona->stages()->sole())->stage->toBe(1)->reached_at->toDateString()->toBe('2026-06-14')
        ->and(SympathizerAssignment::sole())->committee_id->toBe($this->ev['evangelismo']->id)->assigned_by_user_id->toBe($this->bis['presidentaBis']->id);
});

test('BIS tiene que escoger el comité, de la lista de comités activos', function () {
    $this->actingAs($this->bis['presidentaBis'])->post('/sympathizers', $this->persona)
        ->assertSessionHasErrors(['committee_id' => 'Escoja el comité que va a acompañar a la persona.']);
    $this->actingAs($this->bis['presidentaBis'])->post('/sympathizers', $this->persona + ['committee_id' => 999999])
        ->assertSessionHasErrors(['committee_id' => 'Escoja uno de los comités de la lista.']);
});

test('3. un comité registra a su amigo: queda asignado a ese comité, con su nombre como quien lo invitó, y BIS también lo ve', function () {
    $this->actingAs($this->e['liderSonido'])->post('/sympathizers', $this->persona)
        ->assertSessionHas('notice', 'Listo. Andrea Milena Castro quedó registrada y la acompaña Sub-comité de Sonido.');

    expect(Sympathizer::withoutGlobalScopes()->sole())
        ->committee_id->toBe($this->e['sonido']->id)
        ->invited_by_user_id->toBe($this->e['liderSonido']->id);

    $this->actingAs($this->bis['secretarioBis'])->get('/sympathizers')->assertShows('Andrea Milena Castro');
});

test('un comité no escoge otro comité para su amigo', function () {
    $this->actingAs($this->e['liderSonido'])->post('/sympathizers', $this->persona + ['committee_id' => $this->ev['evangelismo']->id])
        ->assertSessionHasErrors(['committee_id' => 'La persona queda en la lista de su comité: no hace falta escoger otro.']);
});

test('4. si ya hay alguien con ese nombre y ese teléfono en otro comité, avisa sin decir dónde, y deja continuar o cancelar', function () {
    simpatizanteDePrueba($this->ev, ['name' => 'Andrea Milena Castro', 'phone' => '3205553400']);

    $this->actingAs($this->e['liderCoro'])->from('/sympathizers/create')->post('/sympathizers', $this->persona)
        ->assertRedirect('/sympathizers/create')
        ->assertSessionHasErrors(['duplicate' => 'Ya hay una persona registrada con el nombre «Andrea Milena Castro» y ese teléfono. Si es la misma, no hace falta registrarla otra vez.']);

    expect(session('errors')->first('duplicate'))->not->toContain('Evangelismo')
        ->and(Sympathizer::withoutGlobalScopes()->count())->toBe(1);

    $this->actingAs($this->e['liderCoro'])->post('/sympathizers', $this->persona + ['confirm_duplicate' => true])->assertSessionHasNoErrors();

    expect(Sympathizer::withoutGlobalScopes()->count())->toBe(2);
});

test('el mismo nombre con otro teléfono no se toma por la misma persona', function () {
    simpatizanteDePrueba($this->ev, ['name' => 'Andrea Milena Castro', 'phone' => '3001112233']);

    $this->actingAs($this->e['liderCoro'])->post('/sympathizers', $this->persona)->assertSessionHasNoErrors();
});

test('el formulario se le abre a cualquier cargo; a BIS, con la lista de comités para escoger', function () {
    $this->actingAs($this->e['liderSonido'])->get('/sympathizers/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Sympathizers/Form')->where('committees', null)->where('ownCommittee', 'Sub-comité de Sonido'));

    $this->actingAs($this->bis['presidentaBis'])->get('/sympathizers/create')
        ->assertInertia(fn (Assert $page) => $page->has('committees')->where('ownCommittee', null));
});
