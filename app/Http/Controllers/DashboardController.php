<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Approval;
use App\Support\Activities\ScheduleCards;
use App\Support\Activities\ScheduleSummary;
use App\Support\Approvals\ApprovalCards;
use App\Support\People\Birthdays;
use App\Support\Scope\CurrentScope;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * La pantalla de inicio (HU-17): primero lo que espera la decision del cargo y desde cuando; luego
 * en que va lo que la persona envio, como va el año y lo que viene. Es una de las dos excepciones
 * declaradas del presupuesto de consultas: lo que no cabe en la primera carga llega aparte, cada
 * cosa en su propia peticion (contracts/pages.md, Inicio).
 */
class DashboardController extends Controller
{
    /** Lo que se muestra de cada lista: lo mas urgente cabe en una pantalla de celular. */
    private const LIST_SIZE = 5;

    public function __construct(private readonly CurrentScope $scope) {}

    public function __invoke(Request $request): Response
    {
        $anio = now(ScheduleCards::ZONE)->year;

        return Inertia::render('dashboard', [
            'pendingApprovals' => fn () => $this->porDecidir(),
            'kpis' => fn () => ['year' => $anio] + ScheduleSummary::of($this->delAnio($anio)->map(ScheduleCards::own(...))),
            'myRequests' => Inertia::defer(fn () => $this->misSolicitudes($request->user()->id), 'mine'),
            'upcoming' => Inertia::defer(fn () => $this->proximas(), 'upcoming'),
            'committeeProgress' => Inertia::defer(fn () => $this->avancePorComite($anio), 'progress'),
            'birthdays' => Inertia::defer(fn () => $this->cumpleanos(), 'birthdays'),
        ]);
    }

    /** Quienes del ambito cumplen años hoy y en la semana que sigue (RF-095, HU-29). */
    private function cumpleanos(): array
    {
        $hoy = now(ScheduleCards::ZONE);

        return ['today' => $hoy->toDateString(), 'people' => Birthdays::soon(Birthdays::people(), $hoy)];
    }

    /** Lo que espera la decision del cargo activo, lo mas antiguo primero (RF-054). */
    private function porDecidir(): array
    {
        $nivel = ApprovalCards::levelDecidedBy($this->scope);

        if ($nivel === null) {
            return [];
        }

        return ApprovalCards::query()
            ->where('approvals.status', Approval::PENDING)
            ->where('approvals.level', $nivel)
            ->get()
            ->sortBy(fn (Approval $solicitud) => $solicitud->escalated_at ?? $solicitud->requested_at)
            ->take(self::LIST_SIZE)
            ->map(ApprovalCards::card(...))
            ->values()
            ->all();
    }

    /** En que va lo que la persona envio: quien la tiene, o como se resolvio y por que (HU-17, escenario 3). */
    private function misSolicitudes(int $persona): array
    {
        return ApprovalCards::query()
            ->where('approvals.requested_by_user_id', $persona)
            ->latest('approvals.requested_at')
            ->limit(self::LIST_SIZE)
            ->get()
            ->map(ApprovalCards::card(...))
            ->all();
    }

    private function proximas(): array
    {
        return ScheduleCards::ownQuery()
            ->where('activities.scheduled_at', '>=', now())
            ->whereIn('activities.status', [Activity::APPROVED, Activity::IN_PROGRESS])
            ->whereNull('activities.cancelled_at')
            ->orderBy('activities.scheduled_at')
            ->limit(self::LIST_SIZE)
            ->get()
            ->map(ScheduleCards::own(...))
            ->all();
    }

    /** Como va cada comite este año: el pastor, por comite de primer nivel; el presidente, por cada parte del suyo. */
    private function avancePorComite(int $anio): array
    {
        if (ApprovalCards::levelDecidedBy($this->scope) === null) {
            return [];
        }

        return $this->delAnio($anio)
            ->groupBy(fn (Activity $actividad) => $this->scope->isPastor()
                ? ($actividad->parent_name ?? $actividad->committee_name)
                : $actividad->committee_name)
            ->map(fn ($grupo, string $comite) => ['committee' => $comite] + ScheduleSummary::of($grupo->map(ScheduleCards::own(...))))
            ->sortBy('committee')
            ->values()
            ->all();
    }

    private function delAnio(int $anio)
    {
        return ScheduleCards::ownQuery()
            ->inGeneralSchedule()
            ->whereBetween('activities.scheduled_at', ScheduleCards::yearRange($anio))
            ->where('activities.status', '!=', Activity::REJECTED)
            ->get();
    }
}
