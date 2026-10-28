import { useDismissedToday } from '@/hooks/use-dismissed-today';
import { turnsText } from '@/lib/birthdays';
import { dayTitle } from '@/lib/calendar';
import { cardClass } from '@/lib/styles';
import type { BirthdayItem, SoonBirthdays } from '@/types/birthdays';
import { Link } from '@inertiajs/react';
import { Cake, X } from 'lucide-react';
import { useState } from 'react';

const linkClass = 'inline-flex min-h-12 items-center text-lg font-semibold text-ipuc underline underline-offset-4';

/**
 * Los cumpleaños al entrar (HU-29 escenarios 1 y 2, RF-095): quién cumple hoy y, al abrirla, quién
 * en los próximos siete días. Se cierra hasta mañana. Llega aparte, un momento después del inicio.
 */
export default function BirthdayAlert({ birthdays }: { birthdays?: SoonBirthdays }) {
    const [dismissed, dismiss] = useDismissedToday('cumpleanos-cerrado', birthdays?.today);
    const [open, setOpen] = useState(false);

    if (!birthdays || dismissed || birthdays.people.length === 0) return null;

    const today = birthdays.people.filter((person) => person.date === birthdays.today);
    const later = birthdays.people.filter((person) => person.date !== birthdays.today);
    const title = today.length === 0 ? 'Cumpleaños de los próximos días' : today.length === 1 ? 'Hoy cumple años' : 'Hoy cumplen años';

    return (
        <section aria-labelledby="cumpleanos-titulo" className={`relative mb-6 border-l-4 border-ipuc-dorado p-4 ${cardClass}`}>
            <button
                type="button"
                onClick={dismiss}
                aria-label="Cerrar los cumpleaños hasta mañana"
                className="absolute top-2 right-2 flex size-12 items-center justify-center rounded-full text-tinta-suave hover:bg-ipuc/5 focus-visible:outline-2 focus-visible:outline-ipuc"
            >
                <X aria-hidden className="size-6" />
            </button>
            <h2 id="cumpleanos-titulo" className="flex items-center gap-3 pr-12 text-xl font-bold">
                <span aria-hidden className="flex size-10 shrink-0 items-center justify-center rounded-full bg-ipuc-dorado text-tinta">
                    <Cake className="size-6" />
                </span>
                {title}
            </h2>

            {today.length > 0 && <People people={today} />}
            {today.length > 0 && later.length > 0 && (
                <button type="button" aria-expanded={open} onClick={() => setOpen(!open)} className={`mt-2 ${linkClass}`}>
                    {open ? 'Ocultar los próximos días' : `Ver los de los próximos días (${later.length})`}
                </button>
            )}
            {(open || today.length === 0) && <People people={later} withDate />}

            <Link href="/birthdays" className={`mt-2 ${linkClass}`}>
                Ver los cumpleaños del mes
            </Link>
        </section>
    );
}

function People({ people, withDate = false }: { people: BirthdayItem[]; withDate?: boolean }) {
    return (
        <ul className="mt-3 flex flex-col gap-2">
            {people.map((person) => (
                <li key={`${person.kind}-${person.id}`} className="text-lg break-words">
                    {withDate && <span className="block font-semibold">{dayTitle(person.date)}</span>}
                    <span className={withDate ? '' : 'font-bold'}>{person.name}</span>, {turnsText(person.turns)}
                </li>
            ))}
        </ul>
    );
}
