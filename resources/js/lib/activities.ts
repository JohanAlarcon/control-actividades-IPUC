import { calendarDate } from '@/lib/format';

/**
 * Lo que deja escoger el calendario: de hoy al 31 de diciembre, en Colombia (CL-07, S-04). Al
 * cargar el plan de un año que todavía no empieza, desde el 1 de enero de ese año.
 */
export function dateLimits(now: Date = new Date(), year?: number): { min: string; max: string } {
    const today = calendarDate(now);
    const start = `${year ?? today.slice(0, 4)}-01-01`;

    return { min: today > start ? today : start, max: `${year ?? today.slice(0, 4)}-12-31` };
}

/** «Se ha usado en 3 actividades»: le dice al pastor cuánto pesa una clase antes de desactivarla. */
export function usesText(uses: number): string {
    if (uses === 0) return 'Todavía no se ha usado';

    return `Se ha usado en ${uses} ${uses === 1 ? 'actividad' : 'actividades'}`;
}
