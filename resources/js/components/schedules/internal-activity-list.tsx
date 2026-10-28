import InfoNote from '@/components/info-note';
import InternalActivityRow from '@/components/schedules/internal-activity-row';
import { cardClass } from '@/lib/styles';
import type { InternalActivity } from '@/types/schedules';
import { CalendarPlus } from 'lucide-react';

interface Props {
    activities: InternalActivity[];
    /** El periodo del cronograma, si quien mira lo lleva: con él mueve o cancela cada fecha. */
    limits: { min: string; max: string } | null;
}

/** Las actividades del cronograma en orden de fecha, con quien las dirige. Sin estado: no pasan por aprobación (P-03). */
export default function InternalActivityList({ activities, limits }: Props) {
    if (activities.length === 0) {
        return <InfoNote icon={CalendarPlus}>Todavía no tiene actividades.</InfoNote>;
    }

    return (
        <ul className={`divide-y divide-ipuc/5 ${cardClass}`}>
            {activities.map((activity) => (
                <InternalActivityRow key={activity.id} activity={activity} limits={limits} />
            ))}
        </ul>
    );
}
