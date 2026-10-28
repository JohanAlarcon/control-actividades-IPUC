import { cellTone, monthWeeks, WEEKDAYS } from '@/lib/calendar';
import { shortTimeRange } from '@/lib/format';
import { lookOf, MONTHS } from '@/lib/schedule';
import type { CalendarActivity, Highlight, MonthNoteItem } from '@/types/schedule';
import { useLayoutEffect, useRef, useState, type CSSProperties } from 'react';

/** En el papel el naranja es el del formato: la hoja no tiene que leerse en una pantalla (D-47). */
const PAPER_TONE: Record<Highlight, string> = {
    special_day: 'text-especial',
    district: 'text-cronograma-naranja',
    national: 'text-cronograma-naranja',
};

interface Props {
    year: number;
    month: number;
    activities: CalendarActivity[];
    notes: MonthNoteItem[];
    zoom: number;
}

/**
 * Una hoja del cronograma de la sede, del tamaño de una carta horizontal (RF-117, D-47): el mes en
 * azul oscuro y el año en dorado, la banda de los días y las casillas con sus colores, y en cada
 * una lo del día, como en el papel. Si un día trae tanto que no cabe, la letra de toda la hoja se
 * encoge un poco: el mes siempre sale en una sola hoja. En la pantalla se ve encogida.
 */
export default function PrintSheet({ year, month, activities, notes, zoom }: Props) {
    const weeks = monthWeeks(year, month, activities);
    const grid = useRef<HTMLDivElement>(null);
    const [scale, setScale] = useState(1);

    useLayoutEffect(() => {
        const crowded = Array.from(grid.current?.querySelectorAll('[data-casilla]') ?? []).some((cell) => cell.scrollHeight > cell.clientHeight + 1);
        if (crowded && scale > 0.6) setScale((current) => Math.round((current - 0.1) * 10) / 10);
    }, [scale, activities]);

    return (
        <article
            style={{ zoom, '--escala': scale } as CSSProperties}
            className="hoja-cronograma mx-auto mb-6 flex h-[8.5in] w-[11in] flex-col bg-white p-[8mm] text-tinta shadow-tarjeta print:mb-0 print:shadow-none"
        >
            <header className="flex items-end justify-between gap-6 pb-3">
                <h2 className="text-[56px] leading-none font-bold tracking-tight text-cronograma [font-variant-caps:small-caps]">{MONTHS[month - 1]}</h2>
                {notes.length > 0 && (
                    <ul className="mr-auto max-w-[3.6in] self-center">
                        {notes.map((note) => (
                            <li key={note.id} className={`text-[13px] leading-tight font-bold uppercase ${note.highlight ? PAPER_TONE[note.highlight] : 'text-cronograma'}`}>
                                {note.text}
                            </li>
                        ))}
                    </ul>
                )}
                <span className="text-[68px] leading-none font-black tracking-wider text-cronograma-naranja">{year}</span>
            </header>

            <div ref={grid} className="grid min-h-0 flex-1 grid-cols-7 border-t border-l border-cronograma" style={{ gridTemplateRows: `auto repeat(${weeks.length}, minmax(0, 1fr))` }}>
                {WEEKDAYS.map((name) => (
                    <span key={name} className="border-r border-b border-cronograma bg-cronograma py-2 text-center text-[17px] font-bold text-white">
                        {name}
                    </span>
                ))}
                {weeks.flat().map((day) => (
                    <div key={day.date} className={`flex min-h-0 flex-col border-r border-b border-cronograma px-1 pt-1 ${cellTone(day)}`}>
                        <div data-casilla className="min-h-0 flex-1 overflow-hidden text-center">
                            {day.activities.map((activity) => (
                                <Entry key={activity.id} activity={activity} />
                            ))}
                        </div>
                        <span className={`self-end pb-0.5 text-[15px] leading-none font-bold ${day.inMonth ? '' : 'text-tinta-suave'}`}>{String(day.day).padStart(2, '0')}</span>
                    </div>
                ))}
            </div>
        </article>
    );
}

function Entry({ activity }: { activity: CalendarActivity }) {
    const { highlight, theme } = lookOf(activity);

    return (
        <p className="mb-1 leading-tight">
            <span className={`block text-[calc(12px*var(--escala))] font-semibold ${highlight ? PAPER_TONE[highlight] : ''}`}>{activity.title}</span>
            {theme && <span className="block text-[calc(11px*var(--escala))] text-tema">{theme}</span>}
            <span className="block text-[calc(10px*var(--escala))]">
                {shortTimeRange(activity.scheduledAt, activity.endsAt)}
                {activity.location && ` · ${activity.location}`}
            </span>
        </p>
    );
}
