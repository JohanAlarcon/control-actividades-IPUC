<?php

use App\Models\Activity;
use App\Models\Committee;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * La identidad de cada comité (T223; RF-083, HU-26): su logo, su lema y sus objetivos. La registra
 * el presidente, la ven quienes tienen el comité en su ámbito y fuera de él solo viaja el nombre.
 * El escenario 2, lo que el comité imprime, llega con la impresión de sus actividades (T228).
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    Storage::fake('local');
    $this->alabanza = $this->e['alabanza'];
});

test('escenario 1: el presidente registra el logo, el lema y los objetivos, con su nombre y la fecha', function () {
    $this->travelTo(Carbon::parse('2026-10-28 09:00', 'America/Bogota'));

    $this->actingAs($this->e['presidente'])->post("/committees/{$this->alabanza->id}/identity", [
        'logo' => UploadedFile::fake()->image('logo.png', 300, 300),
        'motto' => 'Cantad alegres a Dios',
        'objectives' => 'Preparar la alabanza de cada culto.',
    ])->assertRedirect("/committees/{$this->alabanza->id}")->assertSessionHas('notice');

    $comite = $this->alabanza->fresh();
    Storage::disk('local')->assertExists($comite->logo_path);
    expect($comite->logo_path)->toStartWith("committees/{$comite->id}/");

    $this->actingAs($this->e['presidente'])->get("/committees/{$comite->id}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('identity.motto', 'Cantad alegres a Dios')
            ->where('identity.objectives', 'Preparar la alabanza de cada culto.')
            ->where('identity.updatedBy', 'Hermano Rigoberto Páez')
            ->where('identity.updatedAt', fn (string $fecha) => Carbon::parse($fecha)->tz('America/Bogota')->toDateString() === '2026-10-28')
            ->where('identity.logoUrl', fn (string $url) => str_starts_with($url, "/committees/{$comite->id}/logo?v="))
            ->where('canEditIdentity', true));
});

test('el logo se sirve por su ruta, solo a quien tiene el comité en su ámbito', function () {
    $this->actingAs($this->e['presidente'])->post("/committees/{$this->alabanza->id}/identity", ['logo' => UploadedFile::fake()->image('logo.png')]);

    $this->actingAs($this->e['secretaria'])->get("/committees/{$this->alabanza->id}/logo")
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff');
    $this->actingAs($this->e['presidentaDamas'])->get("/committees/{$this->alabanza->id}/logo")->assertNotFound();
    $this->actingAs($this->e['liderSonido'])->get("/committees/{$this->alabanza->id}/logo")->assertNotFound();
});

test('escenario 3: un comité sin logo no trae dirección de logo; la pantalla muestra sus iniciales', function () {
    $this->actingAs($this->e['presidente'])->get("/committees/{$this->alabanza->id}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('identity.logoUrl', null)
            ->where('identity.updatedBy', null)
            ->where('canEditIdentity', true));

    $this->actingAs($this->e['presidente'])->get("/committees/{$this->alabanza->id}/logo")->assertNotFound();
});

test('escenario 4: otro comité ve en el calendario general el nombre del comité y nada más de su identidad', function () {
    $this->travelTo(Carbon::parse('2026-03-10 10:00', 'America/Bogota'));
    $this->actingAs($this->e['presidente'])->post("/committees/{$this->alabanza->id}/identity", [
        'logo' => UploadedFile::fake()->image('logo.png'),
        'motto' => 'Cantad alegres a Dios',
        'objectives' => 'Preparar la alabanza de cada culto.',
    ]);
    Activity::factory()->forCommittee($this->alabanza)->create([
        'title' => 'Noche de alabanza',
        'status' => Activity::APPROVED,
        'scheduled_at' => Carbon::parse('2026-05-24 18:00', 'America/Bogota'),
    ]);

    $this->actingAs($this->e['presidentaDamas'])->get('/activities?year=2026')
        ->assertOk()
        ->assertShows('Noche de alabanza')
        ->assertShows('Comité de Alabanza')
        ->assertDoesntShow('Cantad alegres')
        ->assertDoesntShow('Preparar la alabanza')
        ->assertDoesntShow('logo?v=');

    $this->actingAs($this->e['presidentaDamas'])->get("/committees/{$this->alabanza->id}/identity")->assertNotFound();
    $this->actingAs($this->e['presidentaDamas'])->post("/committees/{$this->alabanza->id}/identity", ['motto' => 'Ajeno'])->assertNotFound();
    expect($this->alabanza->fresh()->motto)->toBe('Cantad alegres a Dios');
});

test('escenario 5: el líder de un sub-comité ve su identidad, pero no la cambia; la registra su presidente', function () {
    $sonido = $this->e['sonido'];
    $this->actingAs($this->e['presidente'])->post("/committees/{$sonido->id}/identity", ['motto' => 'Que todo suene para Dios'])
        ->assertRedirect("/committees/{$sonido->id}");

    $this->actingAs($this->e['liderSonido'])->get("/committees/{$sonido->id}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('identity.motto', 'Que todo suene para Dios')
            ->where('canEditIdentity', false));
    $this->actingAs($this->e['liderSonido'])->get("/committees/{$sonido->id}/identity")->assertForbidden();
    $this->actingAs($this->e['liderSonido'])->post("/committees/{$sonido->id}/identity", ['motto' => 'Otro lema'])->assertForbidden();

    expect($sonido->fresh()->motto)->toBe('Que todo suene para Dios');
});

test('la secretaria, el tesorero y el pastor la ven, pero no la cambian', function () {
    foreach (['secretaria', 'tesorero', 'pastor'] as $cargo) {
        $this->actingAs($this->e[$cargo])->get("/committees/{$this->alabanza->id}")
            ->assertInertia(fn (Assert $page) => $page->where('canEditIdentity', false));
        $this->actingAs($this->e[$cargo])->post("/committees/{$this->alabanza->id}/identity", ['motto' => 'Otro lema'])->assertForbidden();
    }

    expect($this->alabanza->fresh()->motto)->toBeNull();
});

test('cambiar el logo borra el anterior, y quitarlo vuelve a las iniciales', function () {
    $ruta = "/committees/{$this->alabanza->id}/identity";
    $this->actingAs($this->e['presidente'])->post($ruta, ['logo' => UploadedFile::fake()->image('primero.png')]);
    $primero = $this->alabanza->fresh()->logo_path;

    $this->actingAs($this->e['presidente'])->post($ruta, ['logo' => UploadedFile::fake()->image('segundo.webp')]);
    $segundo = $this->alabanza->fresh()->logo_path;
    Storage::disk('local')->assertMissing($primero);
    Storage::disk('local')->assertExists($segundo);

    $this->actingAs($this->e['presidente'])->post($ruta, ['remove_logo' => true]);
    Storage::disk('local')->assertMissing($segundo);
    expect($this->alabanza->fresh()->logo_path)->toBeNull();
});

test('guardar sin logo nuevo conserva el que había', function () {
    $ruta = "/committees/{$this->alabanza->id}/identity";
    $this->actingAs($this->e['presidente'])->post($ruta, ['logo' => UploadedFile::fake()->image('logo.png')]);
    $logo = $this->alabanza->fresh()->logo_path;

    $this->actingAs($this->e['presidente'])->post($ruta, ['motto' => 'Cantad alegres a Dios']);

    expect($this->alabanza->fresh())->logo_path->toBe($logo)->motto->toBe('Cantad alegres a Dios');
    Storage::disk('local')->assertExists($logo);
});

test('el logo tiene que ser una imagen liviana, y el lema y los objetivos cortos', function () {
    $ruta = "/committees/{$this->alabanza->id}/identity";

    $this->actingAs($this->e['presidente'])->post($ruta, ['logo' => UploadedFile::fake()->create('acta.pdf', 100, 'application/pdf')])
        ->assertSessionHasErrors(['logo' => 'El logo tiene que ser una imagen.']);
    $this->actingAs($this->e['presidente'])->post($ruta, ['logo' => UploadedFile::fake()->image('dibujo.svg')])
        ->assertSessionHasErrors('logo');
    $this->actingAs($this->e['presidente'])->post($ruta, ['logo' => UploadedFile::fake()->image('grande.png')->size(3000)])
        ->assertSessionHasErrors(['logo' => 'El logo pesa demasiado. Use una imagen de menos de 2 MB.']);
    $this->actingAs($this->e['presidente'])->post($ruta, ['motto' => str_repeat('a', 161), 'objectives' => str_repeat('a', 2001)])
        ->assertSessionHasErrors([
            'motto' => 'El lema es muy largo. Resúmalo en menos de 160 letras.',
            'objectives' => 'Los objetivos son muy largos. Resúmalos en menos de 2.000 letras.',
        ]);

    expect($this->alabanza->fresh())->logo_path->toBeNull()->motto->toBeNull();
});

test('el formulario y el logo caben en el presupuesto, y no crecen con los datos', function () {
    $this->actingAs($this->e['presidente'])->post("/committees/{$this->alabanza->id}/identity", ['logo' => UploadedFile::fake()->image('logo.png')]);
    $rutas = ["/committees/{$this->alabanza->id}/identity", "/committees/{$this->alabanza->id}/logo"];
    $medir = function (string $ruta) {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($this->e['presidente'])->get($ruta)->assertOk();
        $consultas = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $consultas;
    };

    $antes = array_map($medir, $rutas);

    foreach (range(1, 5) as $vuelta) {
        Committee::factory()->for($this->e['sede'])->childOf($this->alabanza)->create();
    }

    expect(max($antes))->toBeLessThanOrEqual(5)
        ->and(array_map($medir, $rutas))->toBe($antes);
});
