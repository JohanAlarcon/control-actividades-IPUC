import { ZONE } from '@/lib/format';
import type { ActivityStatus, CalendarActivity, Highlight, OwnActivity, ScheduleFilters } from '@/types/schedule';

/** Cómo se dice y cómo se ve cada estado (RF-026). Los tonos salen de la paleta de la IPUC. */
export const STATUS_INFO: Record<ActivityStatus, { label: string; tone: string }> = {
    completed: { label: 'Cumplida', tone: 'bg-ipuc text-white' },
    in_progress: { label: 'En camino', tone: 'bg-ipuc-claro/20 text-ipuc' },
    approved: { label: 'Aprobada, sin empezar', tone: 'bg-white text-tinta ring-1 ring-linea' },
    pending: { label: 'Esperando aprobación', tone: 'bg-ipuc-dorado/25 text-tinta' },
    rejected: { label: 'No aprobada', tone: 'bg-alerta/10 text-alerta' },
};

/** Cómo se dice y de qué color sale cada marca del cronograma de la sede (RF-116, D-47). */
export const HIGHLIGHT_INFO: Record<Highlight, { label: string; text: string }> = {
    special_day: { label: 'Día especial', text: 'text-especial' },
    district: { label: 'Del distrito', text: 'text-distrito' },
    national: { label: 'Nacional', text: 'text-distrito' },
};

const dayAndMonth = new Intl.DateTimeFormat('es-CO', { timeZone: 'UTC', day: 'numeric', month: 'long' });

/**
 * «Del 1 de febrero al 30 de junio de 2026»: el periodo de un cronograma interno. Las fechas llegan
 * sin hora y se leen en UTC, para que el 1 de enero no se vuelva el 31 de diciembre.
 */
export function periodLabel(startsOn: string, endsOn: string): string {
    const [from, to] = [new Date(`${startsOn}T00:00:00Z`), new Date(`${endsOn}T00:00:00Z`)];

    return `Del ${dayAndMonth.format(from)} al ${dayAndMonth.format(to)} de ${to.getUTCFullYear()}`;
}

export const MONTHS = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

/** Lo propio trae su avance; lo ajeno, solo los seis datos compartidos. */
export function isOwn(activity: CalendarActivity): activity is OwnActivity {
    return 'progress' in activity;
}

/**
 * La dirección del cronograma con sus filtros: así una pantalla filtrada se puede compartir
 * (RF-029). Con `/activities/print`, la de su hoja para imprimir (RF-117).
 */
export function scheduleUrl(filters: Partial<ScheduleFilters>, path = '/activities'): string {
    const query = Object.entries(filters)
        .filter(([, value]) => value !== null && value !== undefined)
        .map(([key, value]) => `${key}=${encodeURIComponent(String(value))}`)
        .join('&');

    return query ? `${path}?${query}` : path;
}

const dayNumber = new Intl.DateTimeFormat('es-CO', { timeZone: ZONE, day: 'numeric' });
const weekday = new Intl.DateTimeFormat('es-CO', { timeZone: ZONE, weekday: 'short' });

/** «18» y «sáb.»: el día que muestra la fila de una actividad, en hora de Colombia. */
export function dayParts(iso: string): { day: string; weekday: string } {
    const date = new Date(iso);

    return { day: dayNumber.format(date), weekday: weekday.format(date) };
}

/** La marca y el tema solo los trae lo propio: de lo ajeno no viajan (P-09). */
export function lookOf(activity: CalendarActivity): { highlight: Highlight | null; theme: string | null } {
    return isOwn(activity) ? { highlight: activity.highlight, theme: activity.theme } : { highlight: null, theme: null };
}
