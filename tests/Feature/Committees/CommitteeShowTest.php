<?php

use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->e = estructuraDePrueba();
});

it('la ficha de un comité trae a quienes lo encabezan y sus sub-comités con su líder', function () {
    $this->actingAs($this->e['presidente'])->get("/committees/{$this->e['alabanza']->id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Committees/Show')
            ->where('committee.name', 'Comité de Alabanza')
            ->where('responsible', 'Hermano Rigoberto Páez')
            ->where('secretary', 'Hermana Luz Mary Quintero')
            ->where('treasurer', 'Hermano Jairo Betancur')
            ->where('hasChildren', true)
            ->where('children', fn ($hijos) => collect($hijos)->pluck('responsible', 'name')->sortKeys()->all() === [
                'Sub-comité de Coro' => 'Hermana Omaira Lozano',
                'Sub-comité de Sonido' => 'Hermano Andrés Molina',
            ])
        );
});

it('un comité sin sub-comités lo dice, sin una lista vacía (CL-01)', function () {
    $this->actingAs($this->e['presidentaDamas'])->get("/committees/{$this->e['damas']->id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('hasChildren', false)
            ->where('children', [])
        );
});

it('un comité ajeno responde la página de no disponible, sin un solo dato suyo', function () {
    $this->actingAs($this->e['presidentaDamas'])->get("/committees/{$this->e['alabanza']->id}")
        ->assertNotFound()
        ->assertInertia(fn ($page) => $page->component('Errors/OutOfScope'))
        ->assertDoesntShow('Comité de Alabanza')
        ->assertDoesntShow('Rigoberto');
});

it('un líder no abre el comité del que cuelga su sub-comité', function () {
    $this->actingAs($this->e['liderSonido'])->get("/committees/{$this->e['alabanza']->id}")
        ->assertNotFound()
        ->assertDoesntShow('Comité de Alabanza');
});

it('un sub-comité inactivo no aparece entre los hijos', function () {
    Committee::factory()->for($this->e['sede'])->childOf($this->e['alabanza'])->inactive()->create(['name' => 'Sub-comité Antiguo']);

    $this->actingAs($this->e['presidente'])->get("/committees/{$this->e['alabanza']->id}")
        ->assertInertia(fn ($page) => $page->where('children', fn ($hijos) => collect($hijos)->pluck('name')->doesntContain('Sub-comité Antiguo')));
});

it('una dirección que no lleva un número responde la página de no disponible', function () {
    $this->actingAs($this->e['presidente'])->get('/committees/alabanza')
        ->assertNotFound()
        ->assertInertia(fn ($page) => $page->component('Errors/OutOfScope'));
});

it('la ficha cabe en el presupuesto y no crece con los datos', function () {
    $medir = function () {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($this->e['presidente'])->get("/committees/{$this->e['alabanza']->id}")->assertOk();
        $consultas = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $consultas;
    };

    $antes = $medir();

    foreach (range(1, 8) as $vuelta) {
        $hijo = Committee::factory()->for($this->e['sede'])->childOf($this->e['alabanza'])->create();
        $lider = User::factory()->create(['site_id' => $this->e['sede']->id]);
        CommitteeUser::factory()->forCommittee($hijo)->role(CommitteeUser::LEADER)->create(['user_id' => $lider->id]);
    }

    expect($antes)->toBeLessThanOrEqual(5)
        ->and($medir())->toBe($antes);
});
