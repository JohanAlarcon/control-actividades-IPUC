<?php

use App\Models\Committee;
use App\Support\Committees\SubtreeResolver;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->resolver = new SubtreeResolver();

    $this->comite = Committee::factory()->create();
    $this->subComite = Committee::factory()->childOf($this->comite)->create();
    $this->equipo = Committee::factory()->childOf($this->subComite)->create();

    $this->otraRama = Committee::factory()->childOf($this->comite)->create();
    $this->equipoAjeno = Committee::factory()->childOf($this->otraRama)->create();
});

it('el sub-árbol de un comité trae sus sub-comités y sus equipos', function () {
    $ids = $this->resolver->idsFor($this->comite->id);

    expect($ids)->toContain($this->comite->id, $this->subComite->id, $this->equipo->id, $this->otraRama->id, $this->equipoAjeno->id)
        ->and($ids)->toHaveCount(5);
});

it('el sub-árbol de un sub-comité trae sus equipos y nada de la rama hermana', function () {
    $ids = $this->resolver->idsFor($this->subComite->id);

    expect($ids)->toContain($this->subComite->id, $this->equipo->id)
        ->and($ids)->not->toContain($this->otraRama->id)
        ->and($ids)->not->toContain($this->equipoAjeno->id)
        ->and($ids)->toHaveCount(2);
});

it('el sub-árbol de un equipo es solo él', function () {
    $ids = $this->resolver->idsFor($this->equipo->id);

    expect($ids)->toBe([$this->equipo->id]);
});

it('lo resuelve en una sola consulta', function () {
    DB::enableQueryLog();
    DB::flushQueryLog();

    $this->resolver->idsFor($this->comite->id);

    expect(DB::getQueryLog())->toHaveCount(1);
});

it('el ámbito del pastor son todos los comités de su sede', function () {
    $deOtraSede = Committee::factory()->create();

    $ids = $this->resolver->idsForSite($this->comite->site_id);

    expect($ids)->toHaveCount(5)
        ->and($ids)->not->toContain($deOtraSede->id);
});
