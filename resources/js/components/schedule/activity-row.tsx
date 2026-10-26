import OriginBadge from '@/components/schedule/origin-badge';
import StatusBadge from '@/components/schedule/status-badge';
import { dayParts, isOwn } from '@/lib/schedule';
import type { CalendarActivity } from '@/types/schedule';
import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';

/** Una actividad del cronograma: el día a la izquierda, lo esencial en el centro, y la ficha al tocarla. */
export default function ActivityRow({ activity }: { activity: CalendarActivity }) {
    const { day, weekday, time } = dayParts(activity.scheduledAt);
    const own = isOwn(activity);

    return (
        <li>
            <Link
                href={`/activities/${activity.id}`}
                className="flex items-start gap-3 p-4 hover:bg-ipuc/5 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-ipuc active:bg-ipuc/10"
            >
                <span aria-hidden className="flex w-14 shrink-0 flex-col items-center rounded-xl bg-ipuc/10 py-1 text-ipuc">
                    <span className="text-2xl leading-tight font-bold">{day}</span>
                    <span className="text-lg leading-tight">{weekday}</span>
                </span>
                <span className="min-w-0 flex-1">
                    <span className="block text-lg leading-snug font-bold break-words">{activity.title}</span>
                    <span className="block text-lg text-tinta-suave">{activity.committee}</span>
                    <span className="block text-lg text-tinta-suave">
                        {time}
                        {activity.location && ` · ${activity.location}`}
                    </span>
                    <span className="mt-2 flex flex-wrap gap-2">
                        <StatusBadge status={activity.status} />
                        {own && <OriginBadge fromPlan={activity.fromPlan} />}
                    </span>
                    {own && activity.progress > 0 && (
                        <span className="mt-2 flex items-center gap-2 text-lg">
                            <span className="h-2 flex-1 overflow-hidden rounded-full bg-ipuc/10">
                                <span className="block h-full rounded-full bg-ipuc" style={{ width: `${activity.progress}%` }} />
                            </span>
                            {activity.progress}%
                        </span>
                    )}
                </span>
                <ChevronRight aria-hidden className="mt-1 size-6 shrink-0 text-tinta-suave" />
            </Link>
        </li>
    );
}
