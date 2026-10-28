import InfoNote from '@/components/info-note';
import { activityWhen } from '@/lib/format';
import { cardClass } from '@/lib/styles';
import type { InternalActivity } from '@/types/schedules';
import { CalendarPlus } from 'lucide-react';

/** Las actividades del cronograma en orden de fecha, con quien las dirige. Sin estado: no pasan por aprobación (P-03). */
export default function InternalActivityList({ activities }: { activities: InternalActivity[] }) {
    if (activities.length === 0) {
        return <InfoNote icon={CalendarPlus}>Todavía no tiene actividades.</InfoNote>;
    }

    return (
        <ul className={`divide-y divide-ipuc/5 ${cardClass}`}>
            {activities.map((activity) => (
                <li key={activity.id} className="p-4 text-lg break-words">
                    <p className="font-semibold text-ipuc first-letter:uppercase">{activityWhen(activity.scheduledAt, activity.endsAt)}</p>
                    <p className="font-bold">{activity.title}</p>
                    {activity.theme && <p className="text-tema">«{activity.theme}»</p>}
                    {(activity.type || activity.location) && <p className="text-tinta-suave">{[activity.type, activity.location].filter(Boolean).join(' · ')}</p>}
                    {activity.responsible && <p className="text-tinta-suave">La dirige: {activity.responsible}</p>}
                </li>
            ))}
        </ul>
    );
}
