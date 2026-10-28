<?php

use App\Models\Member;
use App\Models\Sympathizer;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * La autorizacion del manejo de datos (T172 a T175, RF-061), en las dos fichas: miembro y
 * simpatizante. Marcarla exige su fecha y su medio; el conteo de lo que falta es del cargo activo.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->ev = evangelismoDePrueba($this->e);
    $this->travelTo(Carbon::parse('2026-06-15 10:00', 'America/Bogota'));
    $this->comun = ['age_mode' => 'date', 'birth_date' => '1990-01-01', 'gender' => 'f', 'data_consent' => true, 'data_consent_at' => '2026-06-01', 'data_consent_means' => 'verbal'];
});

test('HU-10 escenario 6 y HU-13 escenario 4: marcar la autorización sin fecha o sin medio se rechaza, en miembros y en simpatizantes', function (string $ruta, array $ficha, string $campo) {
    $sin = [$campo => ''] + $ficha + $this->comun;

    $this->actingAs($ruta === '/members' ? $this->e['pastor'] : $this->e['liderSonido'])->post($ruta, $sin + ['committee_ids' => [$this->e['sonido']->id]])
        ->assertSessionHasErrors($campo);
})->with([
    'miembro sin fecha' => ['/members', ['name' => 'Hermana Uno', 'marital_status' => 'single'], 'data_consent_at'],
    'miembro sin medio' => ['/members', ['name' => 'Hermana Dos', 'marital_status' => 'single'], 'data_consent_means'],
    'simpatizante sin fecha' => ['/sympathizers', ['name' => 'Visitante Uno', 'arrival_source' => 'invitation', 'first_contact_at' => '2026-06-10'], 'data_consent_at'],
    'simpatizante sin medio' => ['/sympathizers', ['name' => 'Visitante Dos', 'arrival_source' => 'invitation', 'first_contact_at' => '2026-06-10'], 'data_consent_means'],
]);

test('con su fecha y su medio, la autorización queda guardada en las dos fichas', function () {
    $this->actingAs($this->e['pastor'])->post('/members', ['name' => 'Hermana Uno', 'marital_status' => 'single', 'committee_ids' => [$this->e['sonido']->id]] + $this->comun)
        ->assertSessionHasNoErrors();
    $this->actingAs($this->e['liderSonido'])->post('/sympathizers', ['name' => 'Visitante Uno', 'arrival_source' => 'invitation', 'first_contact_at' => '2026-06-10'] + $this->comun)
        ->assertSessionHasNoErrors();

    expect(Member::withoutGlobalScopes()->sole())->data_consent->toBeTrue()->data_consent_means->toBe('verbal')
        ->and(Sympathizer::withoutGlobalScopes()->sole())->data_consent->toBeTrue()->data_consent_at->toDateString()->toBe('2026-06-01');
});

test('el conteo de fichas sin autorización es del cargo activo, nunca nombres ajenos', function () {
    Member::factory()->forCommittee($this->e['sonido'])->count(2)->create(['data_consent' => false]);
    Member::factory()->forCommittee($this->e['sonido'])->create(['data_consent' => true, 'data_consent_at' => '2026-01-01', 'data_consent_means' => 'signature']);
    Member::factory()->forCommittee($this->e['damas'])->create(['data_consent' => false]);

    $this->actingAs($this->e['liderSonido'])->get('/members')->assertInertia(fn (Assert $page) => $page->where('missingConsent', 2));
    $this->actingAs($this->e['presidentaDamas'])->get('/members')->assertInertia(fn (Assert $page) => $page->where('missingConsent', 1));
    $this->actingAs($this->e['pastor'])->get('/members')->assertInertia(fn (Assert $page) => $page->where('missingConsent', 3));
});

test('quien acompaña a los simpatizantes ve cuántas fichas no tienen la autorización', function () {
    simpatizanteDePrueba($this->ev, ['data_consent' => false]);
    simpatizanteDePrueba($this->ev, ['data_consent' => true, 'data_consent_at' => '2026-06-01', 'data_consent_means' => 'message']);

    $this->actingAs($this->ev['presidenteEvangelismo'])->get('/sympathizers')->assertInertia(fn (Assert $page) => $page->where('missingConsent', 1));
});
