import ProgressBar from '@/components/progress-bar';
import { cardClass } from '@/lib/styles';
import type { ScheduleSummary } from '@/types/schedule';
import { Link } from '@inertiajs/react';

export type Kpis = ScheduleSummary & { year: number };
export type CommitteeProgress = ScheduleSummary & { committee: string };

/** Cómo va el año: el cumplimiento a la fecha del cargo y, para quien decide, el de cada comité. */
export default function YearProgress({ kpis, committees }: { kpis: Kpis; committees?: CommitteeProgress[] }) {
    return (
        <section aria-labelledby="avance" className="mb-6">
            <h2 id="avance" className="mb-3 px-1 text-xl font-bold">
                Cómo va {kpis.year}
            </h2>

            <Link
                href="/activities"
                className={`block p-4 hover:bg-ipuc/5 focus-visible:outline-2 focus-visible:outline-ipuc ${cardClass}`}
            >
                {kpis.compliance === null ? (
                    <span className="text-lg text-tinta-suave">Todavía no hay actividades que ya debieran haberse hecho.</span>
                ) : (
                    <>
                        <span className="flex items-baseline justify-between gap-3">
                            <span className="text-lg font-semibold">Cumplimiento a la fecha</span>
                            <span className="text-3xl font-bold text-ipuc">{kpis.compliance}%</span>
                        </span>
                        <span className="mt-2 block">
                            <ProgressBar value={kpis.compliance} label="Cumplimiento a la fecha" />
                        </span>
                        <span className="mt-2 block text-lg text-tinta-suave">
                            {kpis.completed} cumplidas de {kpis.due} que ya debían hacerse · {kpis.total} en el año
                        </span>
                    </>
                )}
            </Link>

            {committees && committees.length > 0 && (
                <ul className={`mt-3 divide-y divide-ipuc/5 ${cardClass}`}>
                    {committees.map((committee) => (
                        <li key={committee.committee} className="p-4">
                            <p className="flex items-baseline justify-between gap-3 text-lg">
                                <span className="min-w-0 font-semibold break-words">{committee.committee}</span>
                                <span className="shrink-0 font-bold">{committee.compliance === null ? 'Sin fechas vencidas' : `${committee.compliance}%`}</span>
                            </p>
                            {committee.compliance !== null && (
                                <div className="mt-2">
                                    <ProgressBar value={committee.compliance} label={`Cumplimiento de ${committee.committee}`} size="sm" />
                                </div>
                            )}
                        </li>
                    ))}
                </ul>
            )}
        </section>
    );
}
