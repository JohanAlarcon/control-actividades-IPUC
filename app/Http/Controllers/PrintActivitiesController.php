<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Committee;
use App\Support\Activities\ScheduleCards;
use App\Support\Committees\CommitteeOptions;
use App\Support\Scope\CurrentScope;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Lo que un comité imprime y comparte (HU-30, RF-089): sus actividades y las de sus sub-comités en
 * unas fechas, del cronograma general y de los internos, con su logo y su lema (HU-26 escenario 2).
 * Compartir lo arma el celular y lo envía la persona: el sistema no manda nada (D-44, S-01).
 */
class PrintActivitiesController extends Controller
{
    /** Lo más largo que se puede pedir de una vez. */
    private const MAX_DAYS = 366;

    public function __invoke(Request $request, CurrentScope $scope): Response
    {
        $comite = $this->comite($request->integer('committee') ?: $scope->activeRole()?->committee_id);
        $desde = $this->fecha($request->query('from')) ?? now(ScheduleCards::ZONE)->startOfDay();
        $hasta = $this->fecha($request->query('to')) ?? $desde->copy()->addMonthNoOverflow();
        $hasta = $hasta->max($desde)->min($desde->copy()->addDays(self::MAX_DAYS));

        return Inertia::render('Print/Activities', [
            'committee' => ['id' => $comite->id, 'name' => $comite->name, 'motto' => $comite->motto, 'logoUrl' => $comite->logoUrl()],
            'period' => ['from' => $desde->toDateString(), 'to' => $hasta->toDateString()],
            'activities' => fn () => $this->actividades($comite->id, $desde, $hasta),
            'committees' => Inertia::defer(fn () => CommitteeOptions::forScope()),
        ]);
    }

    /** El comité escogido, o el del cargo; el pastor, que no tiene uno, empieza por el primero. Lo ajeno no existe (D-36). */
    private function comite(?int $id): Committee
    {
        return Committee::query()
            ->when($id, fn ($cual) => $cual->whereKey($id), fn ($cual) => $cual->orderBy('depth')->orderBy('position')->orderBy('name'))
            ->firstOrFail(['id', 'name', 'motto', 'logo_path', 'identity_updated_at']);
    }

    /** Lo aprobado del comité y de sus sub-comités, sin las fechas canceladas, en orden. */
    private function actividades(int $comite, Carbon $desde, Carbon $hasta): array
    {
        return ScheduleCards::ownQuery()
            ->where(fn ($cuales) => $cuales->where('activities.committee_id', $comite)->orWhere('committees.parent_id', $comite))
            ->whereNotIn('activities.status', [Activity::PENDING, Activity::REJECTED])
            ->whereNull('activities.cancelled_at')
            ->whereBetween('activities.scheduled_at', [
                $desde->copy()->setTimezone(config('app.timezone')),
                $hasta->copy()->endOfDay()->setTimezone(config('app.timezone')),
            ])
            ->orderBy('activities.scheduled_at')
            ->get()
            ->map(fn (Activity $actividad) => [
                'id' => $actividad->id,
                'title' => $actividad->title,
                'scheduledAt' => $actividad->scheduled_at->toIso8601String(),
                'endsAt' => $actividad->ends_at->toIso8601String(),
                'location' => $actividad->location,
                'committee' => $actividad->committee_name,
                'responsible' => $actividad->responsible_name,
            ])
            ->all();
    }

    /** Una fecha como la manda el campo, «2026-11-07», en hora de Colombia. Lo que no lo sea se ignora. */
    private function fecha(mixed $texto): ?Carbon
    {
        $fecha = is_string($texto) ? DateTimeImmutable::createFromFormat('!Y-m-d', $texto, new DateTimeZone(ScheduleCards::ZONE)) : false;

        return $fecha && $fecha->format('Y-m-d') === $texto ? Carbon::instance($fecha) : null;
    }
}
