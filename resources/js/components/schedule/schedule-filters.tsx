import { MONTHS, scheduleUrl, STATUS_INFO } from '@/lib/schedule';
import { fieldClass } from '@/lib/styles';
import type { ActivityStatus, CommitteeOption, ScheduleFilters as Filters } from '@/types/schedule';
import { Link, router } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';

interface Props {
    filters: Filters;
    years: { from: number; to: number };
    /** Llegan aparte, un momento después de la pantalla. */
    committees?: CommitteeOption[];
}

const STATUSES: ActivityStatus[] = ['completed', 'in_progress', 'approved', 'pending'];

/**
 * El año y los filtros del cronograma: comité o sub-comité, mes y estado (RF-029). Viajan en la
 * dirección, así una pantalla filtrada se puede compartir; y el servidor recalcula los totales.
 */
export default function ScheduleFilters({ filters, years, committees }: Props) {
    function apply(changes: Partial<Filters>) {
        router.get(scheduleUrl({ ...filters, ...changes }), {}, { preserveState: true, preserveScroll: true, replace: true });
    }

    const filtered = filters.committee !== null || filters.month !== null || filters.status !== null;

    return (
        <section aria-label="Filtros" className="mb-6">
            <div className="flex items-center justify-between gap-3">
                <YearLink year={filters.year - 1} enabled={filters.year > years.from} direction="prev" />
                <span className="text-2xl font-bold">{filters.year}</span>
                <YearLink year={filters.year + 1} enabled={filters.year < years.to} direction="next" />
            </div>

            <div className="mt-3 grid gap-3 lg:grid-cols-3">
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
                <select
                    aria-label="Mes"
                    value={filters.month ?? ''}
                    onChange={(event) => apply({ month: event.target.value ? Number(event.target.value) : null })}
                    className={fieldClass}
                >
                    <option value="">Todo el año</option>
                    {MONTHS.map((name, index) => (
                        <option key={name} value={index + 1}>
                            {name}
                        </option>
                    ))}
                </select>
                <select
                    aria-label="Estado"
                    value={filters.status ?? ''}
                    onChange={(event) => apply({ status: (event.target.value || null) as ActivityStatus | null })}
                    className={fieldClass}
                >
                    <option value="">Todos los estados</option>
                    {STATUSES.map((status) => (
                        <option key={status} value={status}>
                            {STATUS_INFO[status].label}
                        </option>
                    ))}
                </select>
            </div>

            {filtered && (
                <Link href={scheduleUrl({ year: filters.year })} className="mt-3 inline-block text-lg font-semibold text-ipuc underline underline-offset-4">
                    Quitar los filtros
                </Link>
            )}
        </section>
    );
}

function YearLink({ year, enabled, direction }: { year: number; enabled: boolean; direction: 'prev' | 'next' }) {
    const Icon = direction === 'prev' ? ChevronLeft : ChevronRight;

    if (!enabled) return <span className="size-12" />;

    return (
        <Link
            href={scheduleUrl({ year })}
            aria-label={`Ver ${year}`}
            className="flex size-12 items-center justify-center rounded-full bg-white text-ipuc shadow-tarjeta focus-visible:outline-2 focus-visible:outline-ipuc"
        >
            <Icon aria-hidden className="size-6" />
        </Link>
    );
}
