import InfoNote from '@/components/info-note';
import ActivityRow from '@/components/schedule/activity-row';
import { cardClass } from '@/lib/styles';
import type { OwnActivity } from '@/types/schedule';
import { Link } from '@inertiajs/react';
import { CalendarDays } from 'lucide-react';

/** Lo que viene: las próximas actividades aprobadas del cargo. Llega un momento después de la pantalla. */
export default function UpcomingList({ items }: { items?: OwnActivity[] }) {
    return (
        <section aria-labelledby="proximas" className="mb-6">
            <h2 id="proximas" className="mb-3 px-1 text-xl font-bold">
                Lo que viene
            </h2>

            {items === undefined ? (
                <p className={`p-4 text-lg text-tinta-suave ${cardClass}`}>Cargando…</p>
            ) : items.length === 0 ? (
                <InfoNote icon={CalendarDays}>No hay actividades aprobadas por delante.</InfoNote>
            ) : (
                <ul className={`divide-y divide-ipuc/5 overflow-hidden ${cardClass}`}>
                    {items.map((activity) => (
                        <ActivityRow key={activity.id} activity={activity} />
                    ))}
                </ul>
            )}

            <Link href="/activities" className="mt-3 inline-block px-1 text-lg font-semibold text-ipuc underline underline-offset-4">
                Ver el cronograma completo
            </Link>
        </section>
    );
}
