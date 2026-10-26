import InfoNote from '@/components/info-note';
import ActivityRow from '@/components/schedule/activity-row';
import PlanBanner from '@/components/schedule/plan-banner';
import ScheduleFilters from '@/components/schedule/schedule-filters';
import SummaryCard from '@/components/schedule/summary-card';
import MainLayout from '@/layouts/main-layout';
import { MONTHS } from '@/lib/schedule';
import { buttonClass, cardClass } from '@/lib/styles';
import type { AnnualPlanInfo, CommitteeOption, MonthGroup, ScheduleFilters as Filters, ScheduleSummary } from '@/types/schedule';
import { Link, usePage } from '@inertiajs/react';
import { CalendarPlus, CalendarX } from 'lucide-react';
import { useState } from 'react';

interface Props {
    filters: Filters;
    years: { from: number; to: number };
    summary: ScheduleSummary;
    months: MonthGroup[];
    canPropose: boolean;
    annualPlan?: AnnualPlanInfo;
    committees?: CommitteeOption[];
}

/**
 * El cronograma del año (HU-09): los doce meses, uno debajo del otro, o todo como una lista. Lo
 * propio se ve completo; lo de los demás comités, con los siete datos que se comparten.
 */
export default function ActivitiesIndex({ filters, years, summary, months, canPropose, annualPlan, committees }: Props) {
    const { errors } = usePage<{ errors: Record<string, string> }>().props;
    const [view, setView] = useState<'year' | 'list'>('year');
    const all = months.flatMap((group) => group.activities);

    return (
        <MainLayout title={`Cronograma ${filters.year}`} subtitle="Lo que hace la congregación en el año">
            {errors.plan && (
                <p role="alert" className="mb-6 rounded-2xl bg-alerta/10 p-4 text-lg font-semibold text-alerta">
                    {errors.plan}
                </p>
            )}

            {annualPlan && <PlanBanner plan={annualPlan} />}

            {canPropose && (
                <Link href="/activities/create" className={`${buttonClass.primary} mb-6 lg:w-fit lg:px-6`}>
                    <CalendarPlus aria-hidden className="mr-2 size-6" />
                    Proponer una actividad
                </Link>
            )}

            <ScheduleFilters filters={filters} years={years} committees={committees} />
            <SummaryCard summary={summary} />

            <div role="group" aria-label="Cómo ver el cronograma" className="mb-4 grid grid-cols-2 gap-2 rounded-2xl bg-white p-1 shadow-tarjeta lg:w-96">
                <ViewButton active={view === 'year'} onClick={() => setView('year')} label="Mes por mes" />
                <ViewButton active={view === 'list'} onClick={() => setView('list')} label="Lista" />
            </div>

            {all.length === 0 ? (
                <InfoNote icon={CalendarX}>No hay actividades con estos filtros.</InfoNote>
            ) : view === 'list' ? (
                <ul className={`divide-y divide-ipuc/5 overflow-hidden ${cardClass}`}>
                    {all.map((activity) => (
                        <ActivityRow key={activity.id} activity={activity} />
                    ))}
                </ul>
            ) : (
                <div className="grid gap-6 lg:grid-cols-2 lg:items-start">
                    {months.map((group) => (
                        <section key={group.month} aria-labelledby={`mes-${group.month}`}>
                            <h2 id={`mes-${group.month}`} className="mb-3 px-1 text-xl font-bold">
                                {MONTHS[group.month - 1]} <span className="font-normal text-tinta-suave">({group.activities.length})</span>
                            </h2>
                            {group.activities.length === 0 ? (
                                <p className="rounded-2xl bg-white/60 p-4 text-lg text-tinta-suave">Sin actividades este mes.</p>
                            ) : (
                                <ul className={`divide-y divide-ipuc/5 overflow-hidden ${cardClass}`}>
                                    {group.activities.map((activity) => (
                                        <ActivityRow key={activity.id} activity={activity} />
                                    ))}
                                </ul>
                            )}
                        </section>
                    ))}
                </div>
            )}
        </MainLayout>
    );
}

function ViewButton({ active, onClick, label }: { active: boolean; onClick: () => void; label: string }) {
    return (
        <button
            type="button"
            aria-pressed={active}
            onClick={onClick}
            className={`h-12 rounded-xl text-lg font-semibold focus-visible:outline-2 focus-visible:outline-ipuc ${active ? 'bg-ipuc text-white' : 'text-ipuc'}`}
        >
            {label}
        </button>
    );
}
