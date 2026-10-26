<?php

use App\Models\Activity;
use App\Models\Approval;
use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\QueryException;

/**
 * Una cuenta nunca se borra: se le retira el acceso y su nombre queda en la historia (RF-006,
 * RN-09, principio III). Se vigila en tres capas, porque las paginas de configuracion del kit
 * dejaban que cualquiera borrara su propia cuenta, y la base arrastraba en cascada sus cargos,
 * sus actividades y sus solicitudes.
 */
beforeEach(function () {
    $this->site = Site::factory()->create();
    $comite = Committee::factory()->for($this->site)->create();

    $this->hermano = User::factory()->create(['site_id' => $this->site->id]);
    CommitteeUser::factory()->forCommittee($comite)->create(['user_id' => $this->hermano->id]);
    $this->actividad = Activity::factory()->forCommittee($comite)->create(['proposed_by_user_id' => $this->hermano->id]);
    $this->solicitud = Approval::factory()->forCommittee($comite)->create(['requested_by_user_id' => $this->hermano->id]);
});

it('nadie puede borrar su propia cuenta ni cambiar su nombre: esas páginas ya no existen', function () {
    $this->actingAs($this->hermano)->delete('/settings/profile', ['password' => 'password'])->assertNotFound();
    $this->actingAs($this->hermano)->patch('/settings/profile', ['name' => 'Otro Nombre'])->assertNotFound();

    expect($this->hermano->fresh())->not->toBeNull()
        ->and($this->hermano->fresh()->name)->not->toBe('Otro Nombre');
});

it('el sistema se niega a borrar una cuenta aunque se lo pidan desde el código', function () {
    expect(fn () => $this->hermano->delete())->toThrow(RuntimeException::class);

    expect(User::find($this->hermano->id))->not->toBeNull();
});

it('la base de datos tampoco deja borrar a quien tiene historia, y la historia queda intacta', function () {
    // Un borrado directo, sin pasar por el modelo: es la ultima capa.
    expect(fn () => User::query()->whereKey($this->hermano->id)->delete())->toThrow(QueryException::class);

    expect(User::find($this->hermano->id))->not->toBeNull()
        ->and(CommitteeUser::where('user_id', $this->hermano->id)->exists())->toBeTrue()
        ->and($this->actividad->fresh())->not->toBeNull()
        ->and($this->solicitud->fresh())->not->toBeNull();
});
