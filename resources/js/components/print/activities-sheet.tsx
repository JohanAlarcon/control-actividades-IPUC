import CommitteeBadge from '@/components/committees/committee-badge';
import { dayTitle } from '@/lib/calendar';
import { timeRange } from '@/lib/format';
import { periodLabel } from '@/lib/schedule';
import { byDay, responsibleOf } from '@/lib/share';
import type { PrintActivity, PrintCommittee, PrintPeriod } from '@/types/print';

interface Props {
    site: string;
    committee: PrintCommittee;
    period: PrintPeriod;
    activities: PrintActivity[];
}

/**
 * La hoja del comité (HU-30 escenario 1): legible en blanco y negro, con el nombre de la sede, el
 * logo y el lema del comité (HU-26 escenario 2), y día por día la hora, el lugar y quién está a cargo.
 */
export default function ActivitiesSheet({ site, committee, period, activities }: Props) {
    return (
        <article className="hoja-actividades mx-auto max-w-2xl bg-white p-5 shadow-tarjeta sm:rounded-2xl sm:p-8 print:max-w-none print:p-0 print:text-black print:shadow-none">
            <header className="flex items-center gap-4 border-b-2 border-tinta pb-4">
                <CommitteeBadge name={committee.name} logoUrl={committee.logoUrl} size="md" />
                <div className="min-w-0">
                    <p className="text-lg font-semibold tracking-wide uppercase">{site}</p>
                    <h2 className="text-2xl font-bold break-words">{committee.name}</h2>
                    {committee.motto && <p className="text-lg break-words italic">«{committee.motto}»</p>}
                </div>
            </header>

            <p className="mt-4 text-xl font-semibold">Actividades {periodLabel(period.from, period.to).toLowerCase()}</p>

            {activities.length === 0 ? (
                <p className="mt-4 text-lg">No hay actividades aprobadas en estas fechas.</p>
            ) : (
                byDay(activities).map((day) => (
                    <section key={day.date} className="mt-6 break-inside-avoid">
                        <h3 className="border-b border-linea pb-1 text-lg font-bold">{dayTitle(day.date)}</h3>
                        <ul className="mt-3 flex flex-col gap-3">
                            {day.activities.map((activity) => (
                                <li key={activity.id} className="text-lg break-words">
                                    <p className="font-bold">
                                        <span className="whitespace-nowrap">{timeRange(activity.scheduledAt, activity.endsAt)}</span> · {activity.title}
                                    </p>
                                    <p>Lugar: {activity.location}</p>
                                    <p>Responsable: {responsibleOf(activity, committee.name)}</p>
                                </li>
                            ))}
                        </ul>
                    </section>
                ))
            )}
        </article>
    );
}
