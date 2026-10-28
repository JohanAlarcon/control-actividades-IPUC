<?php

use App\Models\Committee;
use App\Support\Committees\SubtreeResolver;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->resolver = new SubtreeResolver;

    $this->comite = Committee::factory()->create();
    $this->subComite = Committee::factory()->childOf($this->comite)->create();
    $this->otraRama = Committee::factory()->childOf($this->comite)->create();
    $this->comiteAjeno = Committee::factory()->create();
});

it('el sub-árbol de un comité trae sus sub-comités', function () {
    $ids = $this->resolver->idsFor($this->comite->id);

    expect($ids)->toContain($this->comite->id, $this->subComite->id, $this->otraRama->id)
        ->and($ids)->not->toContain($this->comiteAjeno->id)
        ->and($ids)->toHaveCount(3);
});

it('el sub-árbol de un sub-comité es solo él, sin la rama hermana', function () {
    expect($this->resolver->idsFor($this->subComite->id))->toBe([$this->subComite->id]);
});

it('si ya se sabe que es un sub-comité, ni siquiera consulta la base', function () {
    DB::enableQueryLog();
    DB::flushQueryLog();

    expect($this->resolver->idsFor($this->subComite->id, 2))->toBe([$this->subComite->id])
        ->and(DB::getQueryLog())->toHaveCount(0);
});

it('encuentra el comité de primer nivel de cada nodo sin consultar la base', function () {
    DB::enableQueryLog();
    DB::flushQueryLog();

    expect($this->resolver->rootIdOf($this->comite))->toBe($this->comite->id)
        ->and($this->resolver->rootIdOf($this->subComite))->toBe($this->comite->id)
        ->and(DB::getQueryLog())->toHaveCount(0);
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

    expect($ids)->toHaveCount(3)
        ->and($ids)->not->toContain($deOtraSede->id);
});
