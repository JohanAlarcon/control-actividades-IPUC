<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Models\Member;
use App\Support\Activities\ScheduleCards;
use App\Support\Committees\CommitteeOptions;
use App\Support\Members\MemberFilters;
use App\Support\Reports\MemberStats;
use App\Support\Reports\ReportLabels;
use App\Support\Scope\CurrentScope;
use App\Support\Site\SiteIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Los reportes de los miembros (HU-11, HU-12): las cifras de RF-036 con los filtros de la lista,
 * en gráficos y en tablas, para imprimir y para descargar. Cada cargo obtiene los de su gente; los
 * de toda la congregación, solo el pastor (escenario 4).
 */
class ReportController extends Controller
{
    public function __construct(private readonly CurrentScope $scope) {}

    /** Las cifras llegan aparte, en su propia petición: es la excepción declarada de R-08. */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Member::class);
        $filtros = MemberFilters::fromRequest($request);

        return Inertia::render('Reports/Index', [
            'filters' => $filtros,
            'scopeNotice' => $this->aclaracion(),
            'report' => Inertia::defer(fn () => MemberStats::of($this->cumplen(MemberFilters::scopedMembers(), $filtros)), 'report'),
            'committees' => Inertia::defer(fn () => CommitteeOptions::forScope(), 'committees'),
        ]);
    }

    /** La hoja para imprimir (HU-12 escenario 2): con la sede, la fecha y los filtros en el encabezado. */
    public function print(Request $request): Response
    {
        Gate::authorize('viewAny', Member::class);
        $filtros = MemberFilters::fromRequest($request);
        $todos = MemberFilters::scopedMembers();

        return Inertia::render('Reports/Print', [
            'filters' => $filtros,
            'filterText' => $this->filtrosEnPalabras($todos, $filtros),
            'scopeNotice' => $this->aclaracion(),
            'report' => MemberStats::of($this->cumplen($todos, $filtros)),
        ]);
    }

    /** El archivo (RF-038): las cifras y la lista de quienes cumplen los filtros, nada más. */
    public function download(Request $request): BinaryFileResponse
    {
        Gate::authorize('viewAny', Member::class);
        $filtros = MemberFilters::fromRequest($request);
        $todos = MemberFilters::scopedMembers();
        $miembros = $this->cumplen($todos, $filtros);
        $hoy = now(ScheduleCards::ZONE);

        $encabezado = array_filter([
            SiteIdentity::for($request->user())['name'] ?? 'IPUC',
            'Reporte de miembros del '.$hoy->locale('es')->translatedFormat('j \d\e F \d\e Y'),
            'Filtros: '.$this->filtrosEnPalabras($todos, $filtros),
            $this->aclaracion(),
        ]);

        return Excel::download(new ReportExport($encabezado, MemberStats::of($miembros), $miembros), "reporte-de-miembros-{$hoy->toDateString()}.xlsx");
    }

    private function cumplen(Collection $todos, array $filtros): Collection
    {
        return $todos->filter(fn (Member $miembro) => MemberFilters::matches($miembro, $filtros))->values();
    }

    private function filtrosEnPalabras(Collection $todos, array $filtros): string
    {
        return ReportLabels::describe($filtros, $filtros['committee'] ? MemberFilters::committeeName($todos, $filtros['committee']) : null);
    }

    private function aclaracion(): ?string
    {
        return $this->scope->isPastor() ? null : 'Estas cifras son solo de la gente de su cargo. Las de toda la congregación las ve únicamente el pastor.';
    }
}
