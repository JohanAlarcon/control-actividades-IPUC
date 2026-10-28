import ActivityRow from '@/components/schedule/activity-row';
import { dayLabel, type CalendarDay } from '@/lib/calendar';
import { cardClass } from '@/lib/styles';

/**
 * Lo del día que se tocó en el mes, debajo del calendario (RF-115). Desde 1280 px no hace falta:
 * las actividades ya se leen dentro de cada casilla.
 */
export default function DayAgenda({ day }: { day: CalendarDay }) {
    return (
        <section aria-labelledby="dia-titulo" aria-live="polite" className="mt-6 xl:hidden">
            <h3 id="dia-titulo" className="mb-3 px-1 text-xl font-bold first-letter:uppercase">
                {dayLabel(day.date)}
            </h3>
            {day.activities.length === 0 ? (
                <p className="rounded-2xl bg-white/60 p-4 text-lg text-tinta-suave">Sin actividades este día.</p>
            ) : (
                <ul className={`divide-y divide-ipuc/5 overflow-hidden ${cardClass}`}>
                    {day.activities.map((activity) => (
                        <ActivityRow key={activity.id} activity={activity} showDay={false} />
                    ))}
                </ul>
            )}
        </section>
    );
}
