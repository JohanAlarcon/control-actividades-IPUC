<?php

use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\Site;
use App\Models\User;
use App\Support\Committees\SiteStructure;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/*
 * La preparación de la sede en el servidor (RF-123): la estructura si falta, y una cuenta por cargo
 * sin titular, con el usuario del cargo y del comité y una clave inicial de seis números que la
 * persona cambia al entrar. El cuadro sale una sola vez.
 */

beforeEach(function () {
    Storage::fake('local');
    $this->cuadro = function (): array {
        $archivo = collect(Storage::disk('local')->files())->sole(fn (string $nombre) => str_starts_with($nombre, 'cuentas-'));
        $lineas = array_values(array_filter(explode("\r\n", preg_replace('/^\x{FEFF}/u', '', Storage::disk('local')->get($archivo)))));

        return array_map(fn (string $linea) => explode(';', $linea), array_slice($lineas, 1));
    };
});

test('en una base vacía crea la sede con su estructura y una cuenta por cargo, con clave de seis números', function () {
    $this->artisan('site:prepare')->expectsOutputToContain('cuentas nuevas')->assertSuccessful();

    $subComites = collect(SiteStructure::COMMITTEES)->flatten()->count();
    $cargos = count(SiteStructure::COMMITTEES) * 3 + $subComites;

    expect(Site::sole()->name)->toBe('IPUC La Esmeralda')
        ->and(Committee::withoutGlobalScopes()->count())->toBe(count(SiteStructure::COMMITTEES) + $subComites)
        ->and(User::count())->toBe(1 + $cargos)
        ->and(User::where('must_change_password', false)->count())->toBe(0)
        ->and(User::where('username', 'pastor')->sole()->is_pastor)->toBeTrue()
        ->and(CommitteeUser::withoutGlobalScopes()->whereNull('ended_at')->count())->toBe($cargos);

    $filas = ($this->cuadro)();
    expect($filas)->toHaveCount(1 + $cargos)
        ->and(array_column($filas, 2))->toContain('pastor', 'presidente.alabanza', 'secretario.damas', 'tesorero.obrasocial', 'lider.hijas', 'presidente.misiones1', 'presidente.bis');

    foreach ($filas as [, , $usuario, $clave]) {
        expect($clave)->toMatch('/^\d{6}$/')
            ->and(Hash::check($clave, User::where('username', $usuario)->sole()->password))->toBeTrue();
    }
});

test('correrlo otra vez no crea cuentas ni cambia claves', function () {
    $this->artisan('site:prepare');
    $claves = User::query()->orderBy('id')->pluck('password', 'id')->all();

    $this->artisan('site:prepare')->expectsOutputToContain('Todos los cargos ya tienen cuenta')->assertSuccessful();

    expect(User::query()->orderBy('id')->pluck('password', 'id')->all())->toBe($claves)
        ->and(Committee::withoutGlobalScopes()->count())->toBe(count(SiteStructure::COMMITTEES) + collect(SiteStructure::COMMITTEES)->flatten()->count());
});

test('con la estructura puesta, solo da cuenta a los cargos vacíos, y no repite un usuario', function () {
    $e = estructuraDePrueba();
    User::factory()->create(['site_id' => $e['sede']->id, 'username' => 'secretario.damas']);

    $this->artisan('site:prepare')->assertSuccessful();

    expect(array_column(($this->cuadro)(), 2))->toBe(['secretario.damas2', 'tesorero.damas'])
        ->and(Committee::withoutGlobalScopes()->count())->toBe(4)
        ->and(CommitteeUser::withoutGlobalScopes()->whereNull('ended_at')->where('user_id', User::where('username', 'secretario.damas2')->value('id'))->value('role'))->toBe(CommitteeUser::SECRETARY);
});
