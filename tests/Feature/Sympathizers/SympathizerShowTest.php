<?php

use App\Support\Sympathizers\CommitmentLevel as Compromiso;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

/* La ficha de seguimiento (T117; HU-14 escenarios 2, 3 y 4). */

beforeEach(function () {
    $this->e = estructuraDePrueba();
    $this->ev = evangelismoDePrueba($this->e);
    $this->quien = $this->ev['presidenteEvangelismo'];
});

test('2. muestra en qué etapa va, con la fecha de cada una', function () {
    $persona = simpatizanteDePrueba($this->ev, [], 3);

    $this->actingAs($this->quien)->get("/sympathizers/{$persona->id}")
        ->assertInertia(fn (Assert $page) => $page
            ->component('Sympathizers/Show')
            ->where('sympathizer.stage', 3)
            ->has('stages', 3)
            ->where('stages.2.stage', 3)
            ->where('stages.2.reachedAt', now()->subDays(70)->toDateString()));
});

test('3. el compromiso lo calcula el sistema, y dice con qué', function () {
    $persona = simpatizanteDePrueba($this->ev, [], 1, [3, 8, 12]);

    $this->actingAs($this->quien)->get("/sympathizers/{$persona->id}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('sympathizer.commitment.level', Compromiso::GROWING)
            ->where('sympathizer.commitment.recentContacts', 3)
            ->where('sympathizer.commitment.daysSinceContact', 3)
            ->where('thresholds.recentDays', Compromiso::RECENT_DAYS));
});

test('4. registrar una visita la deja en su historia y el compromiso se recalcula', function () {
    $persona = simpatizanteDePrueba($this->ev, [], 1, [Compromiso::DRIFTING_DAYS + 30]);

    $this->actingAs($this->quien)->post("/sympathizers/{$persona->id}/visits", ['visited_at' => now()->toDateString(), 'type' => 'service', 'note' => 'Vino con su hermana.'])
        ->assertSessionHas('notice', 'Listo. La visita quedó anotada y el compromiso se recalculó.');

    $this->actingAs($this->quien)->get("/sympathizers/{$persona->id}")
        ->assertInertia(fn (Assert $page) => $page->where('sympathizer.commitment.level', Compromiso::STABLE));

    $visitas = $this->actingAs($this->quien)->withHeaders(pedidoParcial('visits', 'Sympathizers/Show'))->get("/sympathizers/{$persona->id}")->json('props.visits');
    expect($visitas[0])->toMatchArray(['type' => 'service', 'note' => 'Vino con su hermana.', 'by' => 'Hermano Gustavo Rengifo']);
});

test('las etapas se anotan en orden y con una fecha que no va hacia atrás', function () {
    $persona = simpatizanteDePrueba($this->ev, [], 1);

    $this->actingAs($this->quien)->post("/sympathizers/{$persona->id}/stages", ['stage' => 3, 'reached_at' => now()->toDateString()])
        ->assertSessionHasErrors(['stage' => 'Las etapas se anotan en orden: primero la que sigue.']);
    $this->actingAs($this->quien)->post("/sympathizers/{$persona->id}/stages", ['stage' => 2, 'reached_at' => now()->subDays(200)->toDateString()])
        ->assertSessionHasErrors('reached_at');
    $this->actingAs($this->quien)->post("/sympathizers/{$persona->id}/stages", ['stage' => 2, 'reached_at' => now()->toDateString()])
        ->assertSessionHas('notice', 'Listo. La etapa quedó anotada con su fecha.');

    expect($persona->fresh()->stage)->toBe(2)->and($persona->stages()->count())->toBe(2);
});

it('la ficha cabe en el presupuesto y no crece con las visitas ni las etapas', function () {
    $persona = simpatizanteDePrueba($this->ev, [], 2, [5, 20]);
    $medir = function () use ($persona) {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($this->quien)->get("/sympathizers/{$persona->id}")->assertOk();
        $consultas = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $consultas;
    };

    $antes = $medir();
    foreach (range(1, 8) as $dias) {
        $persona->visits()->create(['site_id' => $persona->site_id, 'visited_at' => now()->subDays($dias)->toDateString(), 'type' => 'call', 'by_user_id' => $this->quien->id]);
    }

    expect($antes)->toBeLessThanOrEqual(5)->and($medir())->toBe($antes);
});
