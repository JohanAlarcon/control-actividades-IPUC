import HighlightBadge from '@/components/schedule/highlight-badge';
import OriginBadge from '@/components/schedule/origin-badge';
import StatusBadge from '@/components/schedule/status-badge';
import { timeRange } from '@/lib/format';
import { dayParts, HIGHLIGHT_INFO, isOwn, lookOf } from '@/lib/schedule';
import type { CalendarActivity } from '@/types/schedule';
import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';

interface Props {
    activity: CalendarActivity;
    /** En la lista de un día ya se sabe qué día es: no hace falta repetirlo. */
    showDay?: boolean;
}

/**
 * Una actividad del cronograma: el día a la izquierda y lo esencial en el centro. La propia abre su
 * ficha al tocarla; la de otro comité solo se ve, porque su detalle es de ese comité (D-36).
 */
export default function ActivityRow({ activity, showDay = true }: Props) {
    const row = 'flex items-start gap-3 p-4';

    return (
        <li>
            {isOwn(activity) ? (
                <Link
                    href={`/activities/${activity.id}`}
                    className={`${row} hover:bg-ipuc/5 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-ipuc active:bg-ipuc/10`}
                >
                    <Summary activity={activity} showDay={showDay} />
                    <ChevronRight aria-hidden className="mt-1 size-6 shrink-0 text-tinta-suave" />
                </Link>
            ) : (
                <div className={row}>
                    <Summary activity={activity} showDay={showDay} />
                </div>
            )}
        </li>
    );
}

function Summary({ activity, showDay }: Required<Props>) {
    const { day, weekday } = dayParts(activity.scheduledAt);
    const own = isOwn(activity);
    const { highlight, theme } = lookOf(activity);

    return (
        <>
            {showDay && (
                <span aria-hidden className="flex w-14 shrink-0 flex-col items-center rounded-xl bg-ipuc/10 py-1 text-ipuc">
                    <span className="text-2xl leading-tight font-bold">{day}</span>
                    <span className="text-lg leading-tight">{weekday}</span>
                </span>
            )}
            <span className="min-w-0 flex-1">
                <span className={`block text-lg leading-snug font-bold break-words ${highlight ? HIGHLIGHT_INFO[highlight].text : ''}`}>{activity.title}</span>
                {theme && <span className="block text-lg leading-snug break-words text-tema">{theme}</span>}
                <span className="block text-lg text-tinta-suave">{activity.committee}</span>
                <span className="block text-lg text-tinta-suave">
                    {timeRange(activity.scheduledAt, activity.endsAt)}
                    {activity.location && ` · ${activity.location}`}
                </span>
                <span className="mt-2 flex flex-wrap gap-2">
                    <StatusBadge status={activity.status} />
                    {own && <OriginBadge fromPlan={activity.fromPlan} />}
                    {highlight && <HighlightBadge highlight={highlight} />}
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
        </>
    );
}
