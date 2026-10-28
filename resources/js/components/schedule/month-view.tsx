import DayAgenda from '@/components/schedule/day-agenda';
import MonthCalendar from '@/components/schedule/month-calendar';
import MonthNoteForm from '@/components/schedule/month-note-form';
import { defaultMonth, initialDay, monthWeeks } from '@/lib/calendar';
import { calendarDate } from '@/lib/format';
import { lookOf, MONTHS, scheduleUrl } from '@/lib/schedule';
import { buttonClass } from '@/lib/styles';
import type { MonthGroup, MonthNoteItem, ScheduleFilters } from '@/types/schedule';
import type { SharedProps } from '@/types/shared';
import { Link, usePage } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, Printer } from 'lucide-react';
import { useState } from 'react';

interface Props {
    filters: ScheduleFilters;
    years: { from: number; to: number };
    months: MonthGroup[];
    /** Llegan aparte, un momento después del mes. */
    notes?: MonthNoteItem[];
}

/**
 * El cronograma como calendario del mes (RF-029, RF-115): el mes escogido en los filtros o, si no
 * hay ninguno, el de hoy. Pasar de mes cambia el filtro, para que la dirección se pueda compartir.
 */
export default function MonthView({ filters, years, months, notes = [] }: Props) {
    const isPastor = Boolean(usePage<SharedProps>().props.auth.user?.isPastor);
    const today = calendarDate(new Date());
    const month = filters.month ?? defaultMonth(filters.year, today);
    const weeks = monthWeeks(filters.year, month, months.find((group) => group.month === month)?.activities ?? []);
    const days = weeks.flat().filter((day) => day.inMonth);
    const [picked, setPicked] = useState<string | null>(null);
    const selected = days.find((day) => day.date === picked) ?? days.find((day) => day.date === initialDay(weeks, today)) ?? days[0];
    const hasInternal = days.some((day) => day.activities.some((activity) => lookOf(activity).internal));

    return (
        <>
            <MonthNav filters={filters} years={years} month={month} />
            <MonthCalendar
                year={filters.year}
                month={month}
                weeks={weeks}
                selected={selected.date}
                onSelect={setPicked}
                notes={notes.filter((note) => note.month === month)}
                canRemoveNotes={isPastor}
            />
            {hasInternal && (
                <p className="mt-3 flex items-center gap-2 px-1 text-lg text-tinta-suave">
                    <span aria-hidden className="size-3 shrink-0 rounded-full bg-interno" />
                    En verde, lo de los cronogramas internos: los demás comités no lo ven.
                </p>
            )}
            <DayAgenda day={selected} />
            {isPastor && <MonthNoteForm key={`${filters.year}-${month}`} year={filters.year} month={month} />}

            <div className="mt-6 grid gap-3 sm:grid-cols-2">
                <Link href={scheduleUrl({ ...filters, month }, '/activities/print')} className={buttonClass.secondary}>
                    <Printer aria-hidden className="mr-2 size-6" />
                    Imprimir {MONTHS[month - 1].toLowerCase()}
                </Link>
                <Link href={scheduleUrl({ ...filters, month: null }, '/activities/print')} className={buttonClass.secondary}>
                    <Printer aria-hidden className="mr-2 size-6" />
                    Imprimir todo el año
                </Link>
            </div>
        </>
    );
}

function MonthNav({ filters, years, month }: { filters: ScheduleFilters; years: Props['years']; month: number }) {
    const prev = month > 1 ? { year: filters.year, month: month - 1 } : filters.year > years.from ? { year: filters.year - 1, month: 12 } : null;
    const next = month < 12 ? { year: filters.year, month: month + 1 } : filters.year < years.to ? { year: filters.year + 1, month: 1 } : null;
    const link = 'flex h-12 items-center gap-1 rounded-full bg-white px-4 text-lg font-semibold text-ipuc shadow-tarjeta focus-visible:outline-2 focus-visible:outline-ipuc';

    return (
        <nav aria-label="Otros meses" className="mb-4 flex justify-between gap-3">
            {prev ? (
                <Link href={scheduleUrl({ ...filters, ...prev })} preserveScroll className={link}>
                    <ChevronLeft aria-hidden className="size-6" />
                    {MONTHS[prev.month - 1]}
                </Link>
            ) : (
                <span />
            )}
            {next && (
                <Link href={scheduleUrl({ ...filters, ...next })} preserveScroll className={link}>
                    {MONTHS[next.month - 1]}
                    <ChevronRight aria-hidden className="size-6" />
                </Link>
            )}
        </nav>
    );
}
