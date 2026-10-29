<?php

namespace App\Support\Members;

use App\Models\CommitteeMember;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Los filtros combinables de los miembros (RF-035): comité, género, estado civil, bautizado y
 * edad. Los usan la lista y los reportes, para que cuenten lo mismo (RF-038). Dos consultas traen
 * a todos los del alcance con sus listas vigentes; los filtros, la edad y los conteos se sacan de
 * ahí, así que el número de consultas no crece con los filtros ni con los datos.
 */
class MemberFilters
{
    public const AGE_RANGES = ['children' => [0, 12], 'teens' => [13, 17], 'young' => [18, 29], 'adults' => [30, 59], 'seniors' => [60, 150]];

    /** @return array{committee: ?int, gender: ?string, marital: ?string, baptized: ?string, age: ?string} */
    public static function fromRequest(Request $request): array
    {
        return [
            'committee' => $request->integer('committee') ?: null,
            'gender' => in_array($request->query('gender'), ['f', 'm'], true) ? $request->query('gender') : null,
            'marital' => in_array($request->query('marital'), Member::MARITAL_STATUSES, true) ? $request->query('marital') : null,
            'baptized' => in_array($request->query('baptized'), ['yes', 'no'], true) ? $request->query('baptized') : null,
            'age' => array_key_exists((string) $request->query('age'), self::AGE_RANGES) ? $request->query('age') : null,
        ];
    }

    /**
     * Los miembros activos del alcance, en orden de nombre, con sus listas vigentes del alcance. Cada
     * lista trae el nombre de su comité y el del comité de arriba, para nombrar el filtro sin otra consulta.
     */
    public static function scopedMembers(): Collection
    {
        return Member::query()
            ->where('members.active', true)
            ->with(['memberships' => fn ($pertenencias) => $pertenencias->current()
                ->join('committees', 'committees.id', '=', 'committee_member.committee_id')
                ->leftJoin('committees as parents', 'parents.id', '=', 'committees.parent_id')
                ->orderBy('committees.name')
                ->select(['committee_member.*', 'committees.name as committee_name', 'committees.parent_id as committee_parent_id', 'parents.name as committee_parent_name'])])
            ->orderBy('members.name')
            ->get();
    }

    /** Si el miembro cumple los filtros. Por comité cuenta también quien está en uno de sus sub-comités. */
    public static function matches(Member $miembro, array $filtros): bool
    {
        $edad = $miembro->age();
        $rango = $filtros['age'] ? self::AGE_RANGES[$filtros['age']] : null;
        $comites = $miembro->memberships->flatMap(fn (CommitteeMember $pertenencia) => [$pertenencia->committee_id, $pertenencia->committee_parent_id]);

        return (! $filtros['committee'] || $comites->contains($filtros['committee']))
            && (! $filtros['gender'] || $miembro->gender === $filtros['gender'])
            && (! $filtros['marital'] || $miembro->marital_status === $filtros['marital'])
            && (! $filtros['baptized'] || $miembro->baptized === ($filtros['baptized'] === 'yes'))
            && (! $rango || ($edad !== null && $edad >= $rango[0] && $edad <= $rango[1]));
    }

    /** El nombre del comité del filtro, sacado de las listas que ya llegaron; nulo si nadie lo cumple. */
    public static function committeeName(Collection $miembros, int $comite): ?string
    {
        foreach ($miembros->flatMap(fn (Member $miembro) => $miembro->memberships) as $pertenencia) {
            if ($pertenencia->committee_id === $comite) {
                return $pertenencia->committee_name;
            }
            if ($pertenencia->committee_parent_id === $comite) {
                return $pertenencia->committee_parent_name;
            }
        }

        return null;
    }
}
