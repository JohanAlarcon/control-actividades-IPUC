import { dayTitle } from '@/lib/calendar';
import { calendarDate, timeRange } from '@/lib/format';
import { periodLabel } from '@/lib/schedule';
import type { PrintActivity, PrintPeriod } from '@/types/print';

/** Las actividades por día, en hora de Colombia, en el orden en que llegan. */
export function byDay(activities: PrintActivity[]): { date: string; activities: PrintActivity[] }[] {
    const days = new Map<string, PrintActivity[]>();

    for (const activity of activities) {
        const date = calendarDate(new Date(activity.scheduledAt));
        days.set(date, [...(days.get(date) ?? []), activity]);
    }

    return [...days].map(([date, list]) => ({ date, activities: list }));
}

/** La persona que se escribió o, si no, el comité; con su sub-comité, si no es el que se imprime. */
export function responsibleOf(activity: PrintActivity, committee: string): string {
    if (!activity.responsible) return activity.committee;

    return activity.committee === committee ? activity.responsible : `${activity.responsible} (${activity.committee})`;
}

/**
 * El resumen para WhatsApp (RF-089): el comité, las fechas y, día por día, la hora, la actividad, el
 * lugar y quién está a cargo. Los asteriscos son las negrillas de WhatsApp.
 */
export function shareText(site: string, committee: string, period: PrintPeriod, activities: PrintActivity[]): string {
    const lines = [`*${committee}*`, `${site}. Actividades ${periodLabel(period.from, period.to).toLowerCase()}.`];

    if (activities.length === 0) return [...lines, '', 'No hay actividades aprobadas en estas fechas.'].join('\n');

    for (const day of byDay(activities)) {
        lines.push('', `*${dayTitle(day.date)}*`);

        for (const activity of day.activities) {
            lines.push(`• ${timeRange(activity.scheduledAt, activity.endsAt)} · ${activity.title}`, `Lugar: ${activity.location}`, `Responsable: ${responsibleOf(activity, committee)}`);
        }
    }

    return lines.join('\n');
}

/** WhatsApp se abre con el texto escrito y la persona escoge a quién mandarlo: el sistema no envía nada (S-01). */
export function whatsappUrl(text: string): string {
    return `https://wa.me/?text=${encodeURIComponent(text)}`;
}
