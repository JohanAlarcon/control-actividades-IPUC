<?php

use App\Models\Activity;
use App\Models\Approval;
use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * Abrir algo ajeno escribiendo su numero en la direccion tiene que responder lo
 * mismo que si no existiera (T027, RF-008). Cada modelo con ambito se prueba con
 * una ruta propia de esta prueba, sin esperar a que exista su pantalla: lo que se
 * vigila es que el filtro ya este activo cuando Laravel busca el registro.
 */
beforeEach(function () {
    Route::middleware(['web', 'auth'])->group(function () {
        Route::get('/_directo/comite/{committee}', fn (Committee $committee) => $committee->name);
        Route::get('/_directo/actividad/{activity}', fn (Activity $activity) => $activity->title);
        Route::get('/_directo/aprobacion/{approval}', fn (Approval $approval) => $approval->title);
    });

    $this->site = Site::factory()->create();
    $this->damas = Committee::factory()->for($this->site)->create(['name' => 'Comité de Damas Dorcas']);
    $this->alabanza = Committee::factory()->for($this->site)->create(['name' => 'Comité de Alabanza']);

    $this->presidenta = User::factory()->create(['site_id' => $this->site->id]);
    CommitteeUser::factory()->forCommittee($this->damas)->create(['user_id' => $this->presidenta->id]);
});

it('un comité ajeno, abierto por su número, responde como si no existiera', function () {
    $this->actingAs($this->presidenta)->get("/_directo/comite/{$this->alabanza->id}")
        ->assertNotFound()
        ->assertDontSee('Comité de Alabanza');
});

it('una actividad ajena, abierta por su número, responde como si no existiera', function () {
    $ajena = Activity::factory()->forCommittee($this->alabanza)->create(['title' => 'Ensayo general del coro']);

    $this->actingAs($this->presidenta)->get("/_directo/actividad/{$ajena->id}")
        ->assertNotFound()
        ->assertDontSee('Ensayo general del coro');
});

it('una aprobación ajena, abierta por su número, responde como si no existiera', function () {
    $ajena = Approval::factory()->forCommittee($this->alabanza)->create(['title' => 'Compra de micrófonos']);

    $this->actingAs($this->presidenta)->get("/_directo/aprobacion/{$ajena->id}")
        ->assertNotFound()
        ->assertDontSee('Compra de micrófonos');
});

it('lo propio sí se abre: el filtro no bloquea de más', function () {
    $propia = Activity::factory()->forCommittee($this->damas)->create(['title' => 'Reunión de oración de Damas']);

    $this->actingAs($this->presidenta)->get("/_directo/comite/{$this->damas->id}")
        ->assertOk()
        ->assertSee('Comité de Damas Dorcas');
    $this->actingAs($this->presidenta)->get("/_directo/actividad/{$propia->id}")
        ->assertOk()
        ->assertSee('Reunión de oración de Damas');
});

it('un comité de otra sede no se abre, ni siquiera para el pastor', function () {
    $deOtraSede = Committee::factory()->for(Site::factory()->create())->create(['name' => 'Comité de Otra Sede']);
    $pastor = User::factory()->create(['site_id' => $this->site->id, 'is_pastor' => true]);

    $this->actingAs($pastor)->get("/_directo/comite/{$deOtraSede->id}")
        ->assertNotFound()
        ->assertDontSee('Comité de Otra Sede');
});

it('lo ajeno y lo que no existe responden exactamente igual', function () {
    $ajeno = $this->actingAs($this->presidenta)->get("/_directo/comite/{$this->alabanza->id}");
    $inexistente = $this->actingAs($this->presidenta)->get('/_directo/comite/999999');

    // La direccion pedida es lo unico que cambia entre las dos respuestas.
    $pagina = fn ($respuesta) => collect($respuesta->viewData('page'))->except('url')->all();

    expect($ajeno->status())->toBe($inexistente->status())
        ->and($pagina($ajeno))->toBe($pagina($inexistente));
});

it('un miembro ajeno, abierto por su número, responde como si no existiera')->todo();

it('una reunión ajena, abierta por su número, responde como si no existiera')->todo();

it('un movimiento de presupuesto ajeno, abierto por su número, responde como si no existiera')->todo();
