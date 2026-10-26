import { ZONE } from '@/lib/format';
import type { ActivityStatus, CalendarActivity, OwnActivity, ScheduleFilters } from '@/types/schedule';

/** Cómo se dice y cómo se ve cada estado (RF-026). Los tonos salen de la paleta de la IPUC. */
export const STATUS_INFO: Record<ActivityStatus, { label: string; tone: string }> = {
    completed: { label: 'Cumplida', tone: 'bg-ipuc text-white' },
    in_progress: { label: 'En camino', tone: 'bg-ipuc-claro/20 text-ipuc' },
    approved: { label: 'Aprobada, sin empezar', tone: 'bg-white text-tinta ring-1 ring-linea' },
    pending: { label: 'Esperando aprobación', tone: 'bg-ipuc-dorado/25 text-tinta' },
    rejected: { label: 'No aprobada', tone: 'bg-alerta/10 text-alerta' },
};

export const MONTHS = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

/** Lo propio trae su avance; lo ajeno, solo los siete datos compartidos. */
export function isOwn(activity: CalendarActivity): activity is OwnActivity {
    return 'progress' in activity;
}

/** La dirección del cronograma con sus filtros: así una pantalla filtrada se puede compartir (RF-029). */
export function scheduleUrl(filters: Partial<ScheduleFilters>): string {
    const query = Object.entries(filters)
        .filter(([, value]) => value !== null && value !== undefined)
        .map(([key, value]) => `${key}=${encodeURIComponent(String(value))}`)
        .join('&');

    return query ? `/activities?${query}` : '/activities';
}

const dayNumber = new Intl.DateTimeFormat('es-CO', { timeZone: ZONE, day: 'numeric' });
const weekday = new Intl.DateTimeFormat('es-CO', { timeZone: ZONE, weekday: 'short' });
const hour = new Intl.DateTimeFormat('es-CO', { timeZone: ZONE, hour: 'numeric', minute: '2-digit' });

/** «18», «sáb.» y «7:00 p. m.»: lo que muestra la fila de una actividad, en hora de Colombia. */
export function dayParts(iso: string): { day: string; weekday: string; time: string } {
    const date = new Date(iso);

    return { day: dayNumber.format(date), weekday: weekday.format(date), time: hour.format(date) };
}
