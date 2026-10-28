import MonthDay from '@/components/schedule/month-day';
import { WEEKDAYS, type CalendarDay } from '@/lib/calendar';
import { calendarDate } from '@/lib/format';
import { MONTHS } from '@/lib/schedule';

interface Props {
    year: number;
    month: number;
    weeks: CalendarDay[][];
    selected: string;
    onSelect: (date: string) => void;
}

/**
 * El mes con el formato del cronograma de la sede (RF-115, D-47): el nombre del mes en azul oscuro
 * y el año grande en dorado, la banda azul con los días de lunes a domingo y las casillas con sus
 * colores. En el celular ocupa todo el ancho, para que cada casilla alcance a tocarse.
 */
export default function MonthCalendar({ year, month, weeks, selected, onSelect }: Props) {
    const today = calendarDate(new Date());

    return (
        <section aria-labelledby="mes-titulo" className="-mx-4 bg-white shadow-tarjeta lg:mx-0 lg:overflow-hidden lg:rounded-2xl">
            <div className="flex items-end justify-between gap-3 px-4 pt-4 pb-3">
                <h2 id="mes-titulo" className="text-4xl leading-none font-bold tracking-tight text-cronograma [font-variant-caps:small-caps] lg:text-5xl">
                    {MONTHS[month - 1]}
                    <span className="sr-only"> de {year}</span>
                </h2>
                {/* El año grande es adorno, como en el papel: ya se dice en el título del mes. */}
                <span aria-hidden className="text-4xl leading-none font-black tracking-wider text-cronograma-naranja lg:text-6xl">
                    {year}
                </span>
            </div>

            <div aria-hidden className="grid grid-cols-7 bg-cronograma text-white">
                {WEEKDAYS.map((name) => (
                    <span key={name} className="py-2 text-center text-lg font-bold">
                        <span className="xl:hidden">{name.slice(0, 3)}</span>
                        <span className="hidden xl:inline">{name}</span>
                    </span>
                ))}
            </div>

            <div className="grid grid-cols-7 border-r border-cronograma/20">
                {weeks.flat().map((day) => (
                    <MonthDay key={day.date} day={day} selected={day.date === selected} today={day.date === today} onSelect={onSelect} />
                ))}
            </div>
        </section>
    );
}
