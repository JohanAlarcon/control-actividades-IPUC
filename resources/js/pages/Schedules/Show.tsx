import InternalActivityForm from '@/components/schedules/internal-activity-form';
import InternalActivityList from '@/components/schedules/internal-activity-list';
import SectionBlock from '@/components/section-block';
import MainLayout from '@/layouts/main-layout';
import { periodLabel } from '@/lib/schedule';
import { cardClass } from '@/lib/styles';
import type { ActivityTypeOption } from '@/types/activities';
import type { InternalActivity } from '@/types/schedules';

interface Props {
    schedule: { id: number; name: string; committee: string; startsOn: string; endsOn: string };
    activities: InternalActivity[];
    /** Llega después de la pantalla, para no pasarse de cinco consultas: el formulario aparece con ella. */
    types?: ActivityTypeOption[];
    canAdd: boolean;
}

/** Un cronograma interno con sus actividades (HU-27). Su presidente o su líder le agrega más. */
export default function ScheduleShow({ schedule, activities, types, canAdd }: Props) {
    return (
        <MainLayout
            title={schedule.name}
            subtitle={`${schedule.committee} · ${periodLabel(schedule.startsOn, schedule.endsOn)}`}
            back={{ href: '/schedules', label: 'Cronogramas internos' }}
        >
            <div className="lg:grid lg:grid-cols-2 lg:items-start lg:gap-6">
                <SectionBlock title="Sus actividades">
                    <InternalActivityList activities={activities} />
                </SectionBlock>

                {canAdd && types && (
                    <SectionBlock title="Agregar una actividad">
                        <div className={`p-4 ${cardClass}`}>
                            <InternalActivityForm scheduleId={schedule.id} types={types} limits={{ min: schedule.startsOn, max: schedule.endsOn }} />
                        </div>
                    </SectionBlock>
                )}
            </div>
        </MainLayout>
    );
}
