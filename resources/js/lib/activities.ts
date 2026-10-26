import { calendarDate } from '@/lib/format';

/** Lo que deja escoger el calendario al proponer: de hoy al 31 de diciembre de este año, en Colombia (CL-07, S-04). */
export function dateLimits(now: Date = new Date()): { min: string; max: string } {
    const today = calendarDate(now);

    return { min: today, max: `${today.slice(0, 4)}-12-31` };
}

/** «Se ha usado en 3 actividades»: le dice al pastor cuánto pesa una clase antes de desactivarla. */
export function usesText(uses: number): string {
    if (uses === 0) return 'Todavía no se ha usado';

    return `Se ha usado en ${uses} ${uses === 1 ? 'actividad' : 'actividades'}`;
}
