import InfoNote from '@/components/info-note';
import ScheduleForm from '@/components/schedules/schedule-form';
import SectionBlock from '@/components/section-block';
import MainLayout from '@/layouts/main-layout';
import { periodLabel } from '@/lib/schedule';
import { cardClass } from '@/lib/styles';
import type { InternalScheduleItem } from '@/types/schedules';
import { Link } from '@inertiajs/react';
import { CalendarRange, ChevronRight, Lock } from 'lucide-react';

interface Props {
    schedules: InternalScheduleItem[];
    /** El comité para el que se crea uno nuevo; nulo si el cargo no crea cronogramas. */
    createFor: string | null;
}

function countLabel(total: number): string {
    return total === 0 ? 'Sin actividades todavía' : total === 1 ? '1 actividad' : `${total} actividades`;
}

/**
 * Los cronogramas internos (HU-27): los ensayos, las clases y las reuniones de cada comité, aparte
 * del cronograma general. Solo los ven el comité dueño, los comités de arriba y el pastor (RF-086).
 */
export default function SchedulesIndex({ schedules, createFor }: Props) {
    return (
        <MainLayout title="Cronogramas internos" subtitle="Lo de cada comité, aparte del cronograma general">
            <div className="lg:grid lg:grid-cols-2 lg:items-start lg:gap-6">
                <div>
                    <SectionBlock title="Los de su comité">
                        {schedules.length === 0 ? (
                            <InfoNote icon={CalendarRange}>Todavía no hay cronogramas internos.</InfoNote>
                        ) : (
                            <ul className={`divide-y divide-ipuc/5 overflow-hidden ${cardClass}`}>
                                {schedules.map((schedule) => (
                                    <li key={schedule.id}>
                                        <Link
                                            href={`/schedules/${schedule.id}`}
                                            className="flex items-center gap-3 px-4 py-3 hover:bg-ipuc/5 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-ipuc active:bg-ipuc/10"
                                        >
                                            <div className="min-w-0 flex-1 text-lg break-words">
                                                <p className="leading-snug font-bold">{schedule.name}</p>
                                                <p className="text-tinta-suave">{schedule.committee}</p>
                                                <p className="text-tinta-suave">{periodLabel(schedule.startsOn, schedule.endsOn)}</p>
                                                <p className="font-semibold text-ipuc">{countLabel(schedule.activities)}</p>
                                            </div>
                                            <ChevronRight aria-hidden className="size-6 shrink-0 text-tinta-suave" />
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </SectionBlock>
                    <div className="mb-6">
                        <InfoNote icon={Lock}>Los demás comités no los ven, ni saben que existen.</InfoNote>
                    </div>
                </div>

                {createFor && (
                    <SectionBlock title="Nuevo cronograma interno">
                        <ScheduleForm committee={createFor} />
                    </SectionBlock>
                )}
            </div>
        </MainLayout>
    );
}
