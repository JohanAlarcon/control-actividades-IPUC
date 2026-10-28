<?php

use App\Models\CommitteeMember;
use App\Models\Member;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * La lista de cada comité (T214; RF-084, RF-085, CL-28): el cargo agrega a personas ya
 * registradas, con su función, y las quita sin borrar nada. La ficha la corrige solo el pastor.
 */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->travelTo(Carbon::parse('2026-06-15 10:00', 'America/Bogota'));
    $this->deDamas = Member::factory()->forCommittee($this->e['damas'])->create(['name' => 'Hermana Gloria Patiño']);
});

test('un presidente agrega a su lista a alguien ya registrado, con su función en la directiva', function () {
    $this->actingAs($this->e['presidente'])
        ->post("/committees/{$this->e['alabanza']->id}/members", ['member_id' => $this->deDamas->id, 'role' => 'board', 'board_position' => 'vocal'])
        ->assertSessionHas('notice', 'Listo. Hermana Gloria Patiño quedó en la lista de Comité de Alabanza.');

    $this->actingAs($this->e['presidente'])->get('/members')
        ->assertInertia(fn (Assert $page) => $page
            ->where('members.0.name', 'Hermana Gloria Patiño')
            ->where('members.0.committees.0.role', 'board')
            ->where('members.0.committees.0.boardPosition', 'vocal'));
});

test('la directiva pide el cargo; las demás funciones no lo llevan', function () {
    $this->actingAs($this->e['presidente'])
        ->post("/committees/{$this->e['alabanza']->id}/members", ['member_id' => $this->deDamas->id, 'role' => 'board'])
        ->assertSessionHasErrors(['board_position' => 'Escoja su cargo en la directiva.']);

    $this->actingAs($this->e['presidente'])
        ->post("/committees/{$this->e['alabanza']->id}/members", ['member_id' => $this->deDamas->id, 'role' => 'teacher', 'board_position' => 'president']);

    expect(CommitteeMember::where('committee_id', $this->e['alabanza']->id)->sole())->role->toBe('teacher')->board_position->toBeNull();
});

test('nadie está dos veces en la misma lista', function () {
    $this->actingAs($this->e['presidentaDamas'])
        ->post("/committees/{$this->e['damas']->id}/members", ['member_id' => $this->deDamas->id, 'role' => 'member'])
        ->assertSessionHasErrors(['member_id' => 'Hermana Gloria Patiño ya está en la lista de este comité.']);
});

test('no se agrega a la lista de un comité ajeno, ni a alguien que no está en el padrón', function () {
    $this->actingAs($this->e['liderSonido'])
        ->post("/committees/{$this->e['alabanza']->id}/members", ['member_id' => $this->deDamas->id, 'role' => 'member'])
        ->assertNotFound();

    $this->actingAs($this->e['presidente'])
        ->post("/committees/{$this->e['alabanza']->id}/members", ['member_id' => 999999, 'role' => 'member'])
        ->assertSessionHasErrors(['member_id' => 'Esa persona no está en el padrón. Si es nueva, pídale al pastor que la registre.']);
});

test('quitar de la lista cierra la pertenencia: la persona sigue en el padrón y su historia se conserva', function () {
    $this->actingAs($this->e['presidentaDamas'])
        ->delete("/committees/{$this->e['damas']->id}/members/{$this->deDamas->id}")
        ->assertSessionHas('notice', 'Listo. Hermana Gloria Patiño salió de la lista de Comité de Damas Dorcas. Su ficha sigue en el padrón.');

    expect(CommitteeMember::withoutGlobalScopes()->where('member_id', $this->deDamas->id)->sole()->ended_at?->toDateString())->toBe('2026-06-15')
        ->and(Member::withoutGlobalScopes()->find($this->deDamas->id))->not->toBeNull();

    $this->actingAs($this->e['presidentaDamas'])->get('/members')->assertInertia(fn (Assert $page) => $page->where('totalCount', 0));
    $this->actingAs($this->e['pastor'])->get('/members')->assertShows('Hermana Gloria Patiño');
});

test('CL-28: un presidente arma su lista, pero no corrige la ficha de nadie', function () {
    $this->actingAs($this->e['presidentaDamas'])->get("/members/{$this->deDamas->id}/edit")->assertForbidden();
    $this->actingAs($this->e['presidentaDamas'])->put("/members/{$this->deDamas->id}", ['name' => 'Otro nombre'])->assertForbidden();

    $this->actingAs($this->e['presidentaDamas'])->get('/members')
        ->assertInertia(fn (Assert $page) => $page->where('canCreate', false)->where('canManageList', true));
});

test('el pastor corrige la ficha y sus comités: entra a los nuevos y sale de los que desmarca', function () {
    $this->actingAs($this->e['pastor'])->put("/members/{$this->deDamas->id}", [
        'name' => 'Hermana Gloria Patiño',
        'age_mode' => 'date',
        'birth_date' => '1975-03-02',
        'gender' => 'f',
        'marital_status' => 'divorced',
        'notes' => 'Canta en el coro desde niña.',
        'committee_ids' => [$this->e['coro']->id],
    ])->assertSessionHasNoErrors();

    $pertenencias = CommitteeMember::withoutGlobalScopes()->where('member_id', $this->deDamas->id)->get();

    expect($pertenencias->whereNull('ended_at')->pluck('committee_id')->all())->toBe([$this->e['coro']->id])
        ->and($pertenencias->whereNotNull('ended_at')->pluck('committee_id')->all())->toBe([$this->e['damas']->id])
        ->and($this->deDamas->fresh())->marital_status->toBe('divorced')->notes->toBe('Canta en el coro desde niña.');
});
