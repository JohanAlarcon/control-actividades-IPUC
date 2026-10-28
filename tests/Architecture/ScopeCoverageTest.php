<?php

use App\Models\Concerns\BelongsToCommitteeScope;
use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * La red de seguridad del principio I.
 *
 * Un modelo nuevo que guarde datos de la congregacion y olvide el filtro rompe
 * la construccion el mismo dia, no el dia que alguien ve lo que no debe.
 */

/** Unica entrada de la lista blanca: la tabla del paquete de bitacora. */
const TABLAS_SIN_AMBITO = [
    'activity_log',
    'sites',
    'users',
    'cache',
    'cache_locks',
    'jobs',
    'job_batches',
    'failed_jobs',
    'migrations',
    'password_reset_tokens',
    'sessions',
];

/** Tablas que ademas del filtro de sede llevan el del cargo activo (data-model §9). */
const TABLAS_CON_AMBITO_DE_COMITE = [
    'committees',
    'activities',
    'approvals',
    'members',
    'committee_member',
    'sympathizers',
    'schedules',
    'activity_series',
];

function modelosDelProyecto(): array
{
    $modelos = [];

    foreach (glob(app_path('Models/*.php')) as $archivo) {
        $clase = 'App\\Models\\'.Str::before(basename($archivo), '.php');

        if (is_subclass_of($clase, Model::class)) {
            $modelos[] = $clase;
        }
    }

    return $modelos;
}

it('todo modelo con site_id usa el rasgo de sede', function () {
    foreach (modelosDelProyecto() as $clase) {
        $tabla = (new $clase)->getTable();

        if (in_array($tabla, TABLAS_SIN_AMBITO, true) || ! Schema::hasColumn($tabla, 'site_id')) {
            continue;
        }

        expect(in_array(BelongsToSite::class, class_uses_recursive($clase), true))
            ->toBeTrue("El modelo {$clase} guarda site_id y no usa BelongsToSite.");
    }
});

it('las tablas con ámbito de comité usan su rasgo', function () {
    $cubiertas = [];

    foreach (modelosDelProyecto() as $clase) {
        if (in_array(BelongsToCommitteeScope::class, class_uses_recursive($clase), true)) {
            $cubiertas[] = (new $clase)->getTable();
        }
    }

    $faltantes = array_values(array_diff(TABLAS_CON_AMBITO_DE_COMITE, $cubiertas));

    expect($faltantes)->toBeEmpty(
        'Estas tablas necesitan BelongsToCommitteeScope: '.implode(', ', $faltantes)
    );
});

it('la lista blanca tiene una sola tabla de la congregación: la bitácora', function () {
    $deLaCongregacion = array_values(array_filter(
        TABLAS_SIN_AMBITO,
        fn ($t) => ! in_array($t, ['sites', 'users', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'migrations', 'password_reset_tokens', 'sessions'], true)
    ));

    expect($deLaCongregacion)->toBe(['activity_log']);
});
