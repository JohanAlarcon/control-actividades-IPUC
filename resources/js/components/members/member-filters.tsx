import { AGE_RANGES, GENDERS, MARITAL_STATUSES, maritalLabel, membersUrl } from '@/lib/people';
import { fieldClass } from '@/lib/styles';
import type { MemberFilters as Filters } from '@/types/people';
import type { CommitteeOption } from '@/types/schedule';
import { Link, router } from '@inertiajs/react';

interface Props {
    filters: Filters;
    /** Solo los comités del alcance; llegan un momento después de la pantalla. */
    committees?: CommitteeOption[];
}

/** Los filtros combinables de la lista: comité, género, estado civil, bautizado y edad (RF-035). Se quitan de una vez. */
export default function MemberFilters({ filters, committees }: Props) {
    function apply(changes: Partial<Filters>) {
        router.get(membersUrl({ ...filters, ...changes }), {}, { preserveState: true, preserveScroll: true, replace: true });
    }

    const filtered = Object.values(filters).some((value) => value !== null);

    return (
        <section aria-label="Filtros" className="my-4">
            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <select
                    aria-label="Comité"
                    value={filters.committee ?? ''}
                    disabled={!committees}
                    onChange={(event) => apply({ committee: event.target.value ? Number(event.target.value) : null })}
                    className={fieldClass}
                >
                    <option value="">{committees ? 'Todos los comités' : 'Cargando los comités…'}</option>
                    {committees?.map((committee) => (
                        <option key={committee.id} value={committee.id}>
                            {'— '.repeat(committee.depth - 1)}
                            {committee.name}
                        </option>
                    ))}
                </select>
                <select aria-label="Género" value={filters.gender ?? ''} onChange={(event) => apply({ gender: (event.target.value || null) as Filters['gender'] })} className={fieldClass}>
                    <option value="">Hombres y mujeres</option>
                    {GENDERS.map((gender) => (
                        <option key={gender.value} value={gender.value}>
                            {gender.value === 'f' ? 'Mujeres' : 'Hombres'}
                        </option>
                    ))}
                </select>
                <select
                    aria-label="Estado civil"
                    value={filters.marital ?? ''}
                    onChange={(event) => apply({ marital: (event.target.value || null) as Filters['marital'] })}
                    className={fieldClass}
                >
                    <option value="">Cualquier estado civil</option>
                    {MARITAL_STATUSES.map((status) => (
                        <option key={status} value={status}>
                            {maritalLabel(status)}
                        </option>
                    ))}
                </select>
                <select
                    aria-label="Bautismo"
                    value={filters.baptized ?? ''}
                    onChange={(event) => apply({ baptized: (event.target.value || null) as Filters['baptized'] })}
                    className={fieldClass}
                >
                    <option value="">Bautizados y sin bautizar</option>
                    <option value="yes">Bautizados</option>
                    <option value="no">Sin bautizar</option>
                </select>
                <select aria-label="Edad" value={filters.age ?? ''} onChange={(event) => apply({ age: event.target.value || null })} className={fieldClass}>
                    <option value="">Todas las edades</option>
                    {AGE_RANGES.map((range) => (
                        <option key={range.value} value={range.value}>
                            {range.label}
                        </option>
                    ))}
                </select>
            </div>

            {filtered && (
                <Link href="/members" className="mt-3 inline-block text-lg font-semibold text-ipuc underline underline-offset-4">
                    Quitar todos los filtros
                </Link>
            )}
        </section>
    );
}
