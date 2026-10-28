<?php

use App\Models\Member;
use App\Models\Sympathizer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/*
 * Los cumpleaños (T236, T237; HU-29, RF-095): al entrar, los de hoy y los de la semana; y la lista
 * del mes, con fecha y código. Miembros activos y simpatizantes del ámbito, nadie de afuera.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-11-07 10:00', 'America/Bogota'));

    $miembro = fn ($comite, string $nombre, ?string $nacio, array $mas = []) => Member::factory()->forCommittee($comite)->create(['name' => $nombre, 'birth_date' => $nacio] + $mas);
    $simpatizante = fn ($comite, string $nombre, ?string $nacio, array $mas = []) => Sympathizer::factory()->forCommittee($comite)->create(['name' => $nombre, 'birth_date' => $nacio] + $mas);

    $this->rocio = $miembro($this->e['alabanza'], 'Hermana Rocío Vanegas', '1981-11-07');
    $miembro($this->e['sonido'], 'Hermano Fabio Rincón', '1990-11-12');
    $miembro($this->e['damas'], 'Hermana Gloria Pineda', '1985-11-07');
    $miembro($this->e['alabanza'], 'Hermano que ya no está', '1960-11-07', ['active' => false]);
    $miembro($this->e['alabanza'], 'Hermano sin fecha', null, ['age_reported' => 40, 'age_reported_at' => '2026-01-10']);
    $simpatizante($this->e['alabanza'], 'Yesenia Ortiz', '2001-11-20');
    $simpatizante($this->e['alabanza'], 'Simpatizante que ya es miembro', '1999-11-08', ['converted_member_id' => $this->rocio->id]);

    $this->nombres = fn (array $tarjetas) => array_column($tarjetas, 'name');
});

test('escenarios 1 y 2: al entrar, los de hoy y los de la semana del ámbito, en su propia petición', function () {
    DB::flushQueryLog();
    DB::enableQueryLog();
    $cumpleanos = $this->actingAs($this->e['presidente'])->get('/dashboard', pedidoParcial('birthdays'))->assertOk()->json('props.birthdays');
    $consultas = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($cumpleanos['today'])->toBe('2026-11-07')
        ->and($cumpleanos['people'])->toBe([
            ['kind' => 'member', 'id' => $this->rocio->id, 'name' => 'Hermana Rocío Vanegas', 'code' => $this->rocio->fresh()->code, 'date' => '2026-11-07', 'turns' => 45],
            ['kind' => 'member', 'id' => Member::where('name', 'Hermano Fabio Rincón')->value('id'), 'name' => 'Hermano Fabio Rincón', 'code' => Member::where('name', 'Hermano Fabio Rincón')->value('code'), 'date' => '2026-11-12', 'turns' => 36],
        ])
        ->and($consultas)->toBeLessThanOrEqual(5);
});

test('escenarios 3 y 4: la lista del mes con fecha y código, y cuántas fichas no tienen fecha', function () {
    $props = $this->actingAs($this->e['presidente'])->get('/birthdays')->assertOk()->viewData('page')['props'];

    expect($props['month'])->toBe(11)
        ->and($props['year'])->toBe(2026)
        ->and(($this->nombres)($props['people']))->toBe(['Hermana Rocío Vanegas', 'Hermano Fabio Rincón', 'Yesenia Ortiz'])
        ->and(array_column($props['people'], 'code'))->toBe([$this->rocio->fresh()->code, Member::where('name', 'Hermano Fabio Rincón')->value('code'), null])
        ->and($props['withoutDate'])->toBe(1);
});

test('un mes que ya pasó se muestra como viene, con la edad que cumplirán', function () {
    Member::factory()->forCommittee($this->e['alabanza'])->create(['name' => 'Hermano de enero', 'birth_date' => '1970-01-15']);

    $props = $this->actingAs($this->e['presidente'])->get('/birthdays?month=1')->assertOk()->viewData('page')['props'];

    expect($props['year'])->toBe(2027)
        ->and($props['people'])->toHaveCount(1)
        ->and($props['people'][0])->toMatchArray(['name' => 'Hermano de enero', 'date' => '2027-01-15', 'turns' => 57]);
});

test('escenario 5: cada cargo ve solo a los de su ámbito; el pastor, a toda la congregación', function () {
    $mes = fn (string $quien) => ($this->nombres)($this->actingAs($this->e[$quien])->get('/birthdays?month=11')->assertOk()->viewData('page')['props']['people']);

    expect($mes('presidentaDamas'))->toBe(['Hermana Gloria Pineda'])
        ->and($mes('liderSonido'))->toBe(['Hermano Fabio Rincón'])
        ->and($mes('liderCoro'))->toBe([])
        ->and($mes('pastor'))->toBe(['Hermana Gloria Pineda', 'Hermana Rocío Vanegas', 'Hermano Fabio Rincón', 'Yesenia Ortiz']);

    $alerta = $this->actingAs($this->e['presidentaDamas'])->get('/dashboard', pedidoParcial('birthdays'))->json('props.birthdays.people');
    expect(($this->nombres)($alerta))->toBe(['Hermana Gloria Pineda']);
});
