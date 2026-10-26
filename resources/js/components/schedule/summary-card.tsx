import { cardClass } from '@/lib/styles';
import type { ScheduleSummary } from '@/types/schedule';

/**
 * Cómo va lo que se está viendo (RF-030): el cumplimiento a la fecha —de lo que ya debía hacerse,
 * cuánto se cumplió— y cuántas hay en cada estado. Se recalcula con cada filtro.
 */
export default function SummaryCard({ summary }: { summary: ScheduleSummary }) {
    const counts = [
        { label: 'Cumplidas', value: summary.completed },
        { label: 'En camino', value: summary.inProgress },
        { label: 'Aprobadas, sin empezar', value: summary.approved },
        { label: 'Esperando aprobación', value: summary.pending },
    ];

    return (
        <section aria-label="Resumen" className={`mb-6 p-4 ${cardClass}`}>
            {summary.compliance === null ? (
                <p className="text-lg text-tinta-suave">Todavía no hay actividades que ya debieran haberse hecho.</p>
            ) : (
                <>
                    <p className="flex items-baseline justify-between gap-3">
                        <span className="text-lg font-semibold">Cumplimiento a la fecha</span>
                        <span className="text-3xl font-bold text-ipuc">{summary.compliance}%</span>
                    </p>
                    <div
                        role="progressbar"
                        aria-valuenow={summary.compliance}
                        aria-valuemin={0}
                        aria-valuemax={100}
                        aria-label="Cumplimiento a la fecha"
                        className="mt-2 h-3 overflow-hidden rounded-full bg-ipuc/10"
                    >
                        <div className="h-full rounded-full bg-ipuc" style={{ width: `${summary.compliance}%` }} />
                    </div>
                    <p className="mt-2 text-lg text-tinta-suave">
                        {summary.completed} cumplidas de {summary.due} que ya debían hacerse.
                    </p>
                </>
            )}

            <dl className="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                {counts.map((count) => (
                    <div key={count.label} className="rounded-xl bg-fondo p-3">
                        <dt className="text-lg text-tinta-suave">{count.label}</dt>
                        <dd className="text-2xl font-bold">{count.value}</dd>
                    </div>
                ))}
            </dl>
        </section>
    );
}
