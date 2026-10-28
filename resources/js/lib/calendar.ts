import { calendarDate } from '@/lib/format';
import type { CalendarActivity, Highlight } from '@/types/schedule';

/** Una casilla del mes: su fecha en Colombia, si es del mes que se ve, y lo que hay ese día. */
export interface CalendarDay {
    date: string;
    day: number;
    inMonth: boolean;
    /** 0 es el lunes y 6 el domingo: la semana del cronograma de la sede empieza el lunes. */
    weekday: number;
    activities: CalendarActivity[];
}

export const WEEKDAYS = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

const DAY = 24 * 60 * 60 * 1000;

function isoDay(time: number): string {
    return new Date(time).toISOString().slice(0, 10);
}

/**
 * Las semanas del mes, de lunes a domingo, como en el cronograma de la sede: los días del mes
 * anterior y del siguiente completan la primera y la última semana. Cada actividad cae en su día
 * en hora de Colombia, ordenada por hora.
 */
export function monthWeeks(year: number, month: number, activities: CalendarActivity[]): CalendarDay[][] {
    const byDay = new Map<string, CalendarActivity[]>();
    [...activities]
        .sort((a, b) => a.scheduledAt.localeCompare(b.scheduledAt))
        .forEach((activity) => {
            const date = calendarDate(new Date(activity.scheduledAt));
            byDay.set(date, [...(byDay.get(date) ?? []), activity]);
        });

    const first = Date.UTC(year, month - 1, 1);
    const last = Date.UTC(year, month, 0);
    const start = first - ((new Date(first).getUTCDay() + 6) % 7) * DAY;
    const end = last + (6 - ((new Date(last).getUTCDay() + 6) % 7)) * DAY;

    const weeks: CalendarDay[][] = [];
    for (let time = start; time <= end; time += DAY) {
        const date = isoDay(time);
        const index = Math.round((time - start) / DAY);
        if (index % 7 === 0) weeks.push([]);
        weeks[weeks.length - 1].push({
            date,
            day: new Date(time).getUTCDate(),
            inMonth: time >= first && time <= last,
            weekday: index % 7,
            activities: byDay.get(date) ?? [],
        });
    }

    return weeks;
}

/** El fondo de la casilla, como en el papel: columnas alternas, el domingo en crema y lo de otro mes en gris. */
export function cellTone(day: CalendarDay): string {
    if (!day.inMonth) return 'bg-celda-fuera';
    if (day.weekday === 6) return 'bg-celda-domingo';

    return day.weekday % 2 === 1 ? 'bg-celda-azul' : 'bg-white';
}

/** La marca de una actividad en la casilla del celular: rojo, naranja, o el azul del cronograma. */
export function markerTone({ highlight, internal = false }: { highlight: Highlight | null; internal?: boolean }): string {
    if (internal) return 'bg-interno';
    if (highlight === 'special_day') return 'bg-especial';
    if (highlight) return 'bg-cronograma-naranja';

    return 'bg-cronograma';
}

const longDay = new Intl.DateTimeFormat('es-CO', { timeZone: 'UTC', weekday: 'long', day: 'numeric', month: 'long' });

/** «jueves, 7 de mayo»: una fecha de casilla dicha con palabras. */
export function dayLabel(date: string): string {
    return longDay.format(Date.parse(date));
}

/** El mes que se abre si no se escogió ninguno: el de hoy, si es el año en curso; si no, enero. */
export function defaultMonth(year: number, today: string): number {
    return Number(today.slice(0, 4)) === year ? Number(today.slice(5, 7)) : 1;
}

/**
 * El día que se muestra abierto al llegar al mes: hoy, si es de este mes; si no, el primero con
 * actividades; y si no hay ninguna, el primero del mes.
 */
export function initialDay(weeks: CalendarDay[][], today: string): string {
    const days = weeks.flat().filter((day) => day.inMonth);

    return (days.find((day) => day.date === today) ?? days.find((day) => day.activities.length > 0) ?? days[0]).date;
}
