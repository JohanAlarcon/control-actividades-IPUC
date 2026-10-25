<?php

use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->site = Site::factory()->create();
    $this->pastor = User::factory()->create(['site_id' => $this->site->id, 'is_pastor' => true]);
    $this->alabanza = Committee::factory()->for($this->site)->create(['name' => 'Comité de Alabanza']);
});

it('el pastor crea una cuenta con clave temporal que exige cambiarse al entrar', function () {
    $this->actingAs($this->pastor)
        ->post('/users', ['name' => 'Hermano Rigoberto Páez', 'username' => 'rigoberto.paez'])
        ->assertRedirect('/users')
        ->assertSessionHas('temporaryPassword');

    $cuenta = User::where('username', 'rigoberto.paez')->first();

    expect($cuenta)->not->toBeNull()
        ->and($cuenta->site_id)->toBe($this->site->id)
        ->and($cuenta->must_change_password)->toBeTrue()
        ->and($cuenta->active)->toBeTrue()
        ->and($cuenta->is_pastor)->toBeFalse();
});

it('la clave temporal que ve el pastor es la que sirve para entrar', function () {
    $this->actingAs($this->pastor)->post('/users', ['name' => 'Hermana Luz Mary', 'username' => 'luz.mary']);
    $clave = session('temporaryPassword')['password'];
    app('auth')->forgetGuards();
    $this->post('/logout');

    $this->post('/login', ['username' => 'luz.mary', 'password' => $clave]);

    $this->assertAuthenticated();
});

it('crear la cuenta con cargo y comité la deja asignada (HU-02, escenario 1)', function () {
    $this->actingAs($this->pastor)->post('/users', [
        'name' => 'Hermano Gustavo Rengifo',
        'username' => 'gustavo.rengifo',
        'committee_id' => $this->alabanza->id,
        'role' => CommitteeUser::PRESIDENT,
    ])->assertRedirect('/users');

    $cargo = User::where('username', 'gustavo.rengifo')->first()->currentRoles()->first();

    expect($cargo)->not->toBeNull()
        ->and($cargo->committee_id)->toBe($this->alabanza->id)
        ->and($cargo->role)->toBe(CommitteeUser::PRESIDENT);
});

it('si el pastor no escribe el usuario, el sistema lo genera a partir del nombre', function () {
    $this->actingAs($this->pastor)->post('/users', ['name' => 'Hermano Rigoberto Páez'])
        ->assertSessionHas('temporaryPassword');

    expect(User::where('username', 'rigoberto.paez')->exists())->toBeTrue()
        ->and(session('temporaryPassword')['username'])->toBe('rigoberto.paez');
});

it('el usuario generado no lleva tildes, mayúsculas ni el tratamiento', function (string $nombre, string $esperado) {
    $this->actingAs($this->pastor)->post('/users', ['name' => $nombre]);

    expect(User::where('username', $esperado)->exists())->toBeTrue();
})->with([
    ['Hermana Luz Mary Quintero', 'luz.quintero'],
    ['Hermano Ñoño Muñoz', 'nono.munoz'],
    ['Efraín', 'efrain'],
]);

it('dos personas con el mismo nombre reciben usuarios distintos', function () {
    $this->actingAs($this->pastor)->post('/users', ['name' => 'Hermana María Gómez']);
    $this->actingAs($this->pastor)->post('/users', ['name' => 'Hermana María Gómez']);

    expect(User::where('username', 'maria.gomez')->exists())->toBeTrue()
        ->and(User::where('username', 'maria.gomez2')->exists())->toBeTrue();
});

it('un usuario repetido se rechaza con un mensaje claro', function () {
    User::factory()->create(['site_id' => $this->site->id, 'username' => 'maria.gomez']);

    $this->actingAs($this->pastor)
        ->post('/users', ['name' => 'Otra María', 'username' => 'maria.gomez'])
        ->assertSessionHasErrors(['username' => 'Ese usuario ya está tomado. Pruebe agregándole el apellido.']);
});

it('un usuario con mayúsculas, espacios o tildes se rechaza', function (string $usuario) {
    $this->actingAs($this->pastor)
        ->post('/users', ['name' => 'Hermano Prueba', 'username' => $usuario])
        ->assertSessionHasErrors('username');
})->with(['Maria.Gomez', 'maria gomez', 'maría']);

it('un comité sin cargo se rechaza: hay que decir qué cargo', function () {
    $this->actingAs($this->pastor)
        ->post('/users', ['name' => 'Hermano Prueba', 'username' => 'hermano.prueba', 'committee_id' => $this->alabanza->id])
        ->assertSessionHasErrors(['role' => 'Escoja qué cargo va a tener en ese comité.']);

    expect(User::where('username', 'hermano.prueba')->exists())->toBeFalse();
});

it('una persona puede tener dos cargos (HU-02, escenario 4)', function () {
    $sonido = Committee::factory()->for($this->site)->childOf($this->alabanza)->create();
    $persona = User::factory()->create(['site_id' => $this->site->id]);

    $this->actingAs($this->pastor)
        ->post("/committees/{$this->alabanza->id}/roles", ['user_id' => $persona->id, 'role' => CommitteeUser::SECRETARY]);
    $this->actingAs($this->pastor)
        ->post("/committees/{$sonido->id}/roles", ['user_id' => $persona->id, 'role' => CommitteeUser::LEADER]);

    expect($persona->currentRoles()->count())->toBe(2);
});

it('el pastor corrige el nombre y el usuario', function () {
    $cuenta = User::factory()->create(['site_id' => $this->site->id, 'username' => 'nombre.viejo']);

    $this->actingAs($this->pastor)
        ->patch("/users/{$cuenta->id}", ['name' => 'Hermano Corregido', 'username' => 'nombre.nuevo'])
        ->assertRedirect('/users');

    expect($cuenta->fresh())
        ->name->toBe('Hermano Corregido')
        ->username->toBe('nombre.nuevo');
});

it('la lista muestra los cargos vigentes de cada cuenta, no los terminados', function () {
    $persona = User::factory()->create(['site_id' => $this->site->id, 'name' => 'Hermano Andrés Molina']);
    CommitteeUser::factory()->forCommittee($this->alabanza)->create(['user_id' => $persona->id]);
    $otro = Committee::factory()->for($this->site)->create();
    CommitteeUser::factory()->forCommittee($otro)->ended()->create(['user_id' => $persona->id]);

    $this->actingAs($this->pastor)->get('/users')
        ->assertInertia(fn ($page) => $page
            ->component('Users/Index')
            ->where('accounts', function ($cuentas) {
                $cuenta = collect($cuentas)->firstWhere('name', 'Hermano Andrés Molina');

                return count($cuenta['roles']) === 1
                    && $cuenta['roles'][0]['committeeName'] === 'Comité de Alabanza';
            })
        );
});

it('la lista no muestra cuentas de otra sede', function () {
    $otraSede = Site::factory()->create();
    User::factory()->create(['site_id' => $otraSede->id, 'name' => 'Hermano De Otra Sede']);

    $this->actingAs($this->pastor)->get('/users')
        ->assertInertia(fn ($page) => $page
            ->where('accounts', fn ($cuentas) => collect($cuentas)->pluck('name')->doesntContain('Hermano De Otra Sede'))
        );
});

it('la lista cabe en el presupuesto de consultas aunque haya muchas cuentas con cargos', function () {
    foreach (Committee::factory()->for($this->site)->count(6)->create() as $comite) {
        $persona = User::factory()->create(['site_id' => $this->site->id]);
        CommitteeUser::factory()->forCommittee($comite)->create(['user_id' => $persona->id]);
    }
    User::factory()->count(10)->create(['site_id' => $this->site->id]);

    DB::flushQueryLog();
    DB::enableQueryLog();
    $this->actingAs($this->pastor)->get('/users')->assertOk();
    $consultas = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($consultas)->toBeLessThanOrEqual(5);
});

it('la ficha de una cuenta cabe en el presupuesto y no crece con los datos', function () {
    $persona = User::factory()->create(['site_id' => $this->site->id]);
    CommitteeUser::factory()->forCommittee($this->alabanza)->create(['user_id' => $persona->id]);

    $medir = function () use ($persona) {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($this->pastor)->get("/users/{$persona->id}/edit")->assertOk();
        $consultas = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $consultas;
    };

    $antes = $medir();

    foreach (Committee::factory()->for($this->site)->count(8)->create() as $comite) {
        CommitteeUser::factory()->forCommittee($comite)->role(CommitteeUser::SECRETARY)->create(['user_id' => $persona->id]);
    }

    expect($antes)->toBeLessThanOrEqual(5)
        ->and($medir())->toBe($antes);
});
