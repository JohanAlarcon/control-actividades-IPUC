/** Fechas y plata como se dicen en la congregación. Siempre en hora de Colombia, esté donde esté el celular. */
export const ZONE = 'America/Bogota';
const DAY = 24 * 60 * 60 * 1000;

const pesos = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 });
const calendarDay = new Intl.DateTimeFormat('en-CA', { timeZone: ZONE, year: 'numeric', month: '2-digit', day: '2-digit' });
const dayAndMonth = new Intl.DateTimeFormat('es-CO', { timeZone: ZONE, day: 'numeric', month: 'long' });
const fullDate = new Intl.DateTimeFormat('es-CO', { timeZone: ZONE, weekday: 'long', day: 'numeric', month: 'long', hour: 'numeric', minute: '2-digit' });
const longDay = new Intl.DateTimeFormat('es-CO', { timeZone: ZONE, weekday: 'long', day: 'numeric', month: 'long' });
const hours = new Intl.DateTimeFormat('es-CO', { timeZone: ZONE, hour: 'numeric', minute: '2-digit' });

/** «2026-10-26»: el día en Colombia, escrito como lo espera un campo de fecha. */
export function calendarDate(date: Date): string {
    return calendarDay.format(date);
}

/** «350.000» mientras se escribe: deja solo los números y les pone los puntos de mil. */
export function groupThousands(text: string): string {
    return text
        .replace(/\D/g, '')
        .replace(/^0+(?=\d)/, '')
        .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

/** «$ 850.000»: en pesos y sin centavos. */
export function money(amount: number): string {
    return pesos.format(amount);
}

/** «22 de octubre». */
export function shortDate(iso: string): string {
    return dayAndMonth.format(new Date(iso));
}

/** «sábado, 7 de noviembre, 7:00 p. m.»: cuándo es una actividad. */
export function activityDate(iso: string): string {
    return fullDate.format(new Date(iso));
}

/** «5:00 a 6:00 p. m.»: de qué hora a qué hora es una actividad. El «p. m.» nunca se parte en dos renglones. */
export function timeRange(startIso: string, endIso: string): string {
    return hours.formatRange(new Date(startIso), new Date(endIso)).replace(/([ap])\.\s+m\./g, '$1.\u00a0m.');
}

/** «5 a 6 p. m.»: las horas en corto, para la casilla del mes, como en el cronograma de la sede. */
export function shortTimeRange(startIso: string, endIso: string): string {
    return timeRange(startIso, endIso).replace(/:00/g, '');
}

/** «jueves, 7 de mayo, de 5:00 a 6:00 p. m.»: cuándo es una actividad, con su hora de fin. */
export function activityWhen(startIso: string, endIso: string): string {
    return `${longDay.format(new Date(startIso))}, de ${timeRange(startIso, endIso)}`;
}

/**
 * «desde hoy», «desde ayer», «desde hace 4 días» o, pasadas dos semanas, la fecha. Cuenta días
 * del calendario, no tandas de 24 horas: lo que llegó anoche ya es «desde ayer».
 */
export function waitingSince(iso: string, now: Date = new Date()): string {
    const days = Math.round((Date.parse(calendarDate(now)) - Date.parse(calendarDate(new Date(iso)))) / DAY);

    if (days <= 0) return 'desde hoy';
    if (days === 1) return 'desde ayer';
    if (days < 14) return `desde hace ${days} días`;

    return `desde el ${shortDate(iso)}`;
}
