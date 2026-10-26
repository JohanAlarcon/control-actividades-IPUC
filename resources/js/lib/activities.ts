import { calendarDate } from '@/lib/format';

/** Las clases de actividad, con el nombre que se usa en la congregación. Son las de Activity::TYPES. */
export const ACTIVITY_TYPES = [
    { value: 'service', label: 'Culto' },
    { value: 'rehearsal', label: 'Ensayo' },
    { value: 'meeting', label: 'Reunión' },
    { value: 'event', label: 'Evento' },
    { value: 'outing', label: 'Salida' },
    { value: 'visit', label: 'Visita' },
    { value: 'campaign', label: 'Campaña' },
    { value: 'convention', label: 'Convención' },
] as const;

export type ActivityType = (typeof ACTIVITY_TYPES)[number]['value'];

/** Lo que deja escoger el calendario al proponer: de hoy al 31 de diciembre de este año, en Colombia (CL-07, S-04). */
export function dateLimits(now: Date = new Date()): { min: string; max: string } {
    const today = calendarDate(now);

    return { min: today, max: `${today.slice(0, 4)}-12-31` };
}
