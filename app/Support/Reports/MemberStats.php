<?php

namespace App\Support\Reports;

use App\Models\CommitteeMember;
use App\Models\Member;
use App\Support\Members\MemberFilters;
use Illuminate\Support\Collection;

/**
 * Las cifras de los reportes (RF-036, HU-11) sobre los miembros que ya trajo la lista, con el filtro
 * del cargo y los filtros puestos. La edad sale de sus dos fuentes, la fecha o la edad anotada
 * (RF-031); quien no tiene ninguna queda fuera de los cortes por edad y se cuenta aparte. Un miembro
 * de varios comités cuenta una vez en el total y aparece en cada uno de sus comités (escenario 5).
 * Cada grupo trae sus nombres, de ReportLabels.
 */
class MemberStats
{
    /** @param Collection<int, Member> $miembros */
    public static function of(Collection $miembros): array
    {
        $total = $miembros->count();
        $edades = $miembros->map(fn (Member $miembro) => $miembro->age())->filter(fn (?int $edad) => $edad !== null);
        $bautizados = $miembros->where('baptized', true);
        $civil = $miembros->countBy('marital_status');

        return [
            'summary' => [
                'total' => $total,
                'baptized' => $bautizados->count(),
                'baptizedPercent' => $total === 0 ? 0 : (int) round($bautizados->count() * 100 / $total),
                'married' => $civil->get('married', 0),
                'single' => $civil->get('single', 0),
                'averageAge' => $edades->isEmpty() ? null : (int) round($edades->avg()),
                'withoutCommittee' => $miembros->filter(fn (Member $miembro) => $miembro->memberships->isEmpty())->count(),
                'withoutAge' => $total - $edades->count(),
                'missingConsent' => $miembros->where('data_consent', false)->count(),
                'pendingProfiles' => $miembros->where('profile_pending', true)->count(),
            ],
            'groups' => [
                self::group('marital', 'Estado civil', self::each(ReportLabels::MARITAL, $civil)),
                self::group('committees', 'Por comité', self::committees($miembros)),
                self::group('ages', 'Por edad', collect(MemberFilters::AGE_RANGES)
                    ->map(fn (array $rango, string $clave) => ['label' => ReportLabels::AGES[$clave], 'count' => $edades->filter(fn (int $edad) => $edad >= $rango[0] && $edad <= $rango[1])->count()])
                    ->values()
                    ->all()),
                self::group('gender', 'Por género', self::each(ReportLabels::GENDER, $miembros->countBy('gender'))),
                self::group('baptized', 'Bautizados', [['label' => ReportLabels::BAPTIZED['yes'], 'count' => $bautizados->count()], ['label' => ReportLabels::BAPTIZED['no'], 'count' => $total - $bautizados->count()]]),
                self::group('baptismsByYear', 'Bautismos por año', $bautizados
                    ->filter(fn (Member $miembro) => $miembro->baptized_at !== null)
                    ->countBy(fn (Member $miembro) => $miembro->baptized_at->year)
                    ->sortKeys()
                    ->map(fn (int $cuantos, int $anio) => ['label' => (string) $anio, 'count' => $cuantos])
                    ->values()
                    ->all()),
            ],
        ];
    }

    private static function group(string $clave, string $titulo, array $filas): array
    {
        return ['key' => $clave, 'title' => $titulo, 'rows' => $filas];
    }

    /** Cada valor con su conteo, incluidos los que no tienen a nadie: un grupo vacío es cero (CL-12). */
    private static function each(array $nombres, Collection $conteos): array
    {
        return collect($nombres)->map(fn (string $nombre, string $valor) => ['label' => $nombre, 'count' => $conteos->get($valor, 0)])->values()->all();
    }

    /** Los comités con gente, del que más tiene al que menos. */
    private static function committees(Collection $miembros): array
    {
        return $miembros
            ->flatMap(fn (Member $miembro) => $miembro->memberships)
            ->groupBy('committee_id')
            ->map(fn (Collection $listas) => [
                'label' => $listas->first()->committee_name,
                'count' => $listas->unique(fn (CommitteeMember $lista) => $lista->member_id)->count(),
            ])
            ->sortBy([['count', 'desc'], ['label', 'asc']])
            ->values()
            ->all();
    }
}
