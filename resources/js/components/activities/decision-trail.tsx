import { shortDate } from '@/lib/format';
import { cardClass } from '@/lib/styles';
import type { TrailEvent } from '@/types/schedule';

/**
 * El rastro de decisiones (principio III): quién la propuso, quién le dio el visto bueno, quién la
 * aprobó o por qué no, y quién la dio por cumplida. Llega un momento después de la ficha.
 */
export default function DecisionTrail({ events }: { events?: TrailEvent[] }) {
    return (
        <section aria-labelledby="rastro" className="mb-6">
            <h2 id="rastro" className="mb-3 px-1 text-xl font-bold">
                Lo que ha pasado
            </h2>
            <div className={`p-4 ${cardClass}`}>
                {events === undefined ? (
                    <p className="text-lg text-tinta-suave">Cargando…</p>
                ) : (
                    <ol className="flex flex-col gap-4">
                        {events.map((event, index) => (
                            <li key={index} className="flex gap-3">
                                <span aria-hidden className="mt-2 size-3 shrink-0 rounded-full bg-ipuc-claro" />
                                <span className="min-w-0">
                                    <span className="block text-lg font-semibold">{event.text}</span>
                                    <span className="block text-lg text-tinta-suave">{shortDate(event.date)}</span>
                                    {event.reason && <span className="mt-1 block text-lg">Motivo: {event.reason}</span>}
                                </span>
                            </li>
                        ))}
                    </ol>
                )}
            </div>
        </section>
    );
}
