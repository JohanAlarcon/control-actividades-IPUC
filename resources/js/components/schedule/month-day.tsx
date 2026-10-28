import { cellTone, dayLabel, markerTone, type CalendarDay } from '@/lib/calendar';
import { shortTimeRange } from '@/lib/format';
import { HIGHLIGHT_INFO, isOwn, lookOf } from '@/lib/schedule';
import type { CalendarActivity } from '@/types/schedule';
import { Link } from '@inertiajs/react';

interface Props {
    day: CalendarDay;
    selected: boolean;
    today: boolean;
    onSelect: (date: string) => void;
}

function countText(count: number): string {
    if (count === 0) return 'sin actividades';

    return count === 1 ? 'una actividad' : `${count} actividades`;
}

/**
 * Una casilla del mes, como en el cronograma de la sede: el color de su columna y el número abajo
 * a la derecha. En el celular lleva una marca por actividad y se toca para ver el día debajo;
 * desde 1280 px las actividades se escriben dentro de la casilla, como en el papel (RF-115).
 */
export default function MonthDay({ day, selected, today, onSelect }: Props) {
    const label = dayLabel(day.date);

    return (
        <div className={`relative min-h-14 border-b border-l border-cronograma/20 ${cellTone(day)} xl:min-h-32 xl:px-1.5 xl:pt-2 xl:pb-8`}>
            {day.inMonth && (
                <button
                    type="button"
                    onClick={() => onSelect(day.date)}
                    aria-pressed={selected}
                    aria-label={`${label}: ${countText(day.activities.length)}`}
                    className={`absolute inset-0 focus-visible:outline-3 focus-visible:-outline-offset-3 focus-visible:outline-ipuc-claro xl:hidden ${selected ? 'ring-3 ring-cronograma ring-inset' : ''}`}
                />
            )}

            <span aria-hidden className="flex flex-col gap-1 p-1.5 xl:hidden">
                {day.activities.slice(0, 3).map((activity) => (
                    <span key={activity.id} className={`h-1.5 rounded-full ${markerTone(lookOf(activity).highlight)}`} />
                ))}
            </span>

            {day.activities.length > 0 && (
                <ul className="hidden flex-col gap-3 text-center xl:flex">
                    {day.activities.map((activity) => (
                        <Entry key={activity.id} activity={activity} label={label} />
                    ))}
                </ul>
            )}

            <span
                aria-hidden
                className={`absolute right-1.5 bottom-1 text-lg leading-none font-bold ${day.inMonth ? 'text-tinta' : 'text-tinta-suave'} ${today ? 'rounded-full bg-cronograma px-1.5 py-1 text-white' : ''}`}
            >
                {String(day.day).padStart(2, '0')}
            </span>
        </div>
    );
}

/** Lo que se escribe en la casilla grande: el nombre en su color, el tema en azul y las horas. */
function Entry({ activity, label }: { activity: CalendarActivity; label: string }) {
    const { highlight, theme } = lookOf(activity);
    const content = (
        <>
            <span className="sr-only">{label}: </span>
            <span className={`block leading-tight font-semibold break-words ${highlight ? HIGHLIGHT_INFO[highlight].text : ''}`}>{activity.title}</span>
            {theme && <span className="block leading-tight break-words text-tema">{theme}</span>}
            <span className="block leading-tight text-tinta-suave">{shortTimeRange(activity.scheduledAt, activity.endsAt)}</span>
        </>
    );

    return (
        <li className="text-lg">
            {isOwn(activity) ? (
                <Link href={`/activities/${activity.id}`} className="block rounded-lg p-0.5 hover:bg-ipuc/5 focus-visible:outline-2 focus-visible:outline-ipuc">
                    {content}
                </Link>
            ) : (
                content
            )}
        </li>
    );
}
