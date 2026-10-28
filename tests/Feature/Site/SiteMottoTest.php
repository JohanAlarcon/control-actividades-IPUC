<?php

use App\Models\SiteMotto;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * El nombre y el lema de la sede (T221, T222; HU-25, RF-081, RF-082, CL-40): la entrada y todas
 * las pantallas los muestran; el pastor registra el lema de cada año, y el primero de enero se ve
 * el nuevo, sin el viejo y sin un espacio vacío.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->e['sede']->update(['name' => 'IPUC La Esmeralda', 'city' => 'Ibagué']);
    $this->travelTo(Carbon::parse('2026-12-20 10:00', 'America/Bogota'));
    $this->guardar = fn (array $datos) => $this->actingAs($this->e['pastor'])->post('/site-motto', $datos);
});

test('1. la entrada, sin sesión, muestra el nombre de la sede y el lema del año en curso con su cita', function () {
    ($this->guardar)(['year' => 2026, 'text' => 'Unánimes', 'citation' => 'Filipenses 2:2']);
    auth()->logout();

    $this->get('/login')->assertInertia(fn (Assert $page) => $page
        ->where('site.name', 'IPUC La Esmeralda')
        ->where('site.city', 'Ibagué')
        ->where('site.motto', ['year' => 2026, 'text' => 'Unánimes', 'citation' => 'Filipenses 2:2']));
});

test('2. ya adentro, el nombre y el lema siguen en cualquier pantalla', function (string $pantalla) {
    ($this->guardar)(['year' => 2026, 'text' => 'Unánimes', 'citation' => 'Filipenses 2:2']);

    $this->actingAs($this->e['liderSonido'])->get($pantalla)
        ->assertInertia(fn (Assert $page) => $page->where('site.name', 'IPUC La Esmeralda')->where('site.motto.text', 'Unánimes'));
})->with(['/dashboard', '/activities', '/members']);

test('3. el pastor registra el lema del año que viene, se ve desde el primero de enero, y el anterior queda en la historia', function () {
    ($this->guardar)(['year' => 2026, 'text' => 'Unánimes', 'citation' => 'Filipenses 2:2']);
    ($this->guardar)(['year' => 2027, 'text' => 'Firmes en la fe', 'citation' => '1 Corintios 16:13'])
        ->assertSessionHas('notice', 'Listo. El lema de 2027 quedó guardado. Se verá desde el primero de enero.');

    $this->actingAs($this->e['liderSonido'])->get('/dashboard')->assertInertia(fn (Assert $page) => $page->where('site.motto.text', 'Unánimes'));

    $this->travelTo(Carbon::parse('2027-01-01 00:05', 'America/Bogota'));

    $this->actingAs($this->e['liderSonido'])->get('/dashboard')->assertOk()->assertInertia(fn (Assert $page) => $page->where('site.motto.text', 'Firmes en la fe'));
    $this->actingAs($this->e['pastor'])->get('/site-motto')->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('mottos', 2)->where('mottos.0.year', 2027)->where('mottos.0.current', true)->where('mottos.1.text', 'Unánimes'));
});

test('4. CL-40: si el año empezó sin lema, solo se ve el nombre, sin el lema viejo', function () {
    ($this->guardar)(['year' => 2026, 'text' => 'Unánimes', 'citation' => 'Filipenses 2:2']);
    $this->travelTo(Carbon::parse('2027-01-02 09:00', 'America/Bogota'));

    $this->actingAs($this->e['liderSonido'])->get('/dashboard')
        ->assertInertia(fn (Assert $page) => $page->where('site.name', 'IPUC La Esmeralda')->where('site.motto', null));
});

test('corregir el lema del año lo cambia enseguida en todas las pantallas', function () {
    ($this->guardar)(['year' => 2026, 'text' => 'Unanimes', 'citation' => null]);
    $this->actingAs($this->e['liderSonido'])->get('/dashboard');
    ($this->guardar)(['year' => 2026, 'text' => 'Unánimes', 'citation' => 'Filipenses 2:2']);

    $this->actingAs($this->e['liderSonido'])->get('/dashboard')->assertInertia(fn (Assert $page) => $page->where('site.motto.text', 'Unánimes'));
    expect(SiteMotto::count())->toBe(1);
});

test('solo el pastor registra el lema, y solo para este año o el que viene', function () {
    $this->actingAs($this->e['presidente'])->get('/site-motto')->assertForbidden();
    $this->actingAs($this->e['presidente'])->post('/site-motto', ['year' => 2026, 'text' => 'Otro lema'])->assertForbidden();

    ($this->guardar)(['year' => 2025, 'text' => 'Viejo'])->assertSessionHasErrors(['year' => 'El lema se registra para 2026 o para 2027.']);
    ($this->guardar)(['year' => 2026, 'text' => ''])->assertSessionHasErrors(['text' => 'Escriba el lema.']);

    expect(SiteMotto::count())->toBe(0);
});
