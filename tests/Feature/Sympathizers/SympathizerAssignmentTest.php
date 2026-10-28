<?php

use App\Models\SympathizerAssignment;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Reasignar a un simpatizante (T216; RF-097, HU-14 escenario 5, CL-29): lo hace BIS; deja de verlo
 * el comité anterior, lo ve el nuevo con toda su historia, y cada asignación queda escrita. Si su
 * comité se inactiva, vuelve a BIS.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->ev = evangelismoDePrueba($this->e);
    $this->bis = bisDePrueba($this->e);
    $this->persona = simpatizanteDePrueba($this->ev, ['name' => 'Andrea Milena Castro']);
});

test('5. BIS la reasigna: el comité anterior deja de verla, el nuevo la ve con su historia, y queda escrito', function () {
    $this->persona->visits()->create(['site_id' => $this->persona->site_id, 'visited_at' => now()->subDays(3)->toDateString(), 'type' => 'call', 'by_user_id' => $this->ev['presidenteEvangelismo']->id]);

    $this->actingAs($this->bis['presidentaBis'])->post("/sympathizers/{$this->persona->id}/assign", ['committee_id' => $this->e['damas']->id])
        ->assertSessionHas('notice', 'Listo. Andrea Milena Castro quedó asignada a Comité de Damas Dorcas. El comité anterior ya no la ve.');

    $this->actingAs($this->ev['presidenteEvangelismo'])->get("/sympathizers/{$this->persona->id}")->assertNotFound();
    $this->actingAs($this->e['presidentaDamas'])->get("/sympathizers/{$this->persona->id}")->assertOk();

    expect($this->persona->fresh()->visits()->count())->toBe(1)
        ->and(SympathizerAssignment::where('sympathizer_id', $this->persona->id)->latest('id')->first())
        ->committee_id->toBe($this->e['damas']->id)
        ->assigned_by_user_id->toBe($this->bis['presidentaBis']->id);
});

test('la ficha cuenta por qué comités pasó', function () {
    $this->actingAs($this->bis['presidentaBis'])->post("/sympathizers/{$this->persona->id}/assign", ['committee_id' => $this->e['damas']->id]);

    $historia = $this->actingAs($this->e['presidentaDamas'])
        ->withHeaders(pedidoParcial('assignments', 'Sympathizers/Show'))
        ->get("/sympathizers/{$this->persona->id}")
        ->json('props.assignments');

    expect(collect($historia)->pluck('committee')->last())->toBe('Comité de Damas Dorcas')
        ->and(collect($historia)->last()['by'])->toBe('Hermana Yolanda Pérez');
});

test('solo BIS y el pastor reasignan; el comité asignado no', function () {
    $this->actingAs($this->ev['presidenteEvangelismo'])->post("/sympathizers/{$this->persona->id}/assign", ['committee_id' => $this->e['damas']->id])->assertForbidden();
    $this->actingAs($this->ev['presidenteEvangelismo'])->get("/sympathizers/{$this->persona->id}")->assertInertia(fn (Assert $page) => $page->where('canAssign', false));
    $this->actingAs($this->e['pastor'])->post("/sympathizers/{$this->persona->id}/assign", ['committee_id' => $this->e['damas']->id])->assertSessionHasNoErrors();
});

test('reasignar al mismo comité no deja otra fila', function () {
    $this->actingAs($this->bis['presidentaBis'])->post("/sympathizers/{$this->persona->id}/assign", ['committee_id' => $this->ev['evangelismo']->id])
        ->assertSessionHasErrors(['committee_id' => 'Ya está asignada a ese comité.']);
});

test('CL-29: si su comité se inactiva, vuelve a BIS con toda su historia, y lo hace el sistema', function () {
    $this->ev['evangelismo']->update(['active' => false]);

    expect($this->persona->fresh()->committee_id)->toBe($this->bis['bis']->id)
        ->and(SympathizerAssignment::where('sympathizer_id', $this->persona->id)->latest('id')->first())
        ->committee_id->toBe($this->bis['bis']->id)
        ->assigned_by_user_id->toBeNull()
        ->and($this->persona->stages()->count())->toBe(1);
});

test('una asignación no se cambia ni se borra', function () {
    $this->actingAs($this->bis['presidentaBis'])->post("/sympathizers/{$this->persona->id}/assign", ['committee_id' => $this->e['damas']->id]);
    $asignacion = SympathizerAssignment::latest('id')->first();

    expect(fn () => $asignacion->update(['committee_id' => $this->ev['evangelismo']->id]))->toThrow(RuntimeException::class)
        ->and(fn () => $asignacion->delete())->toThrow(RuntimeException::class);
});
