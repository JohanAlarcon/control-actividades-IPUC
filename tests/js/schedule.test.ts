import { dateLimits } from '@/lib/activities';
import { dayParts, isOwn, scheduleUrl, STATUS_INFO } from '@/lib/schedule';
import type { OwnActivity, SharedActivity } from '@/types/schedule';
import { describe, expect, it } from 'vitest';

const shared: SharedActivity = {
    id: 1,
    title: 'Retiro de damas',
    description: null,
    scheduledAt: '2026-08-18T19:00:00-05:00',
    location: 'Templo central',
    committee: 'Comité de Damas Dorcas',
    status: 'approved',
};

describe('scheduleUrl', () => {
    it('lleva los filtros en la dirección, sin los que están vacíos', () => {
        expect(scheduleUrl({ year: 2026, committee: 5, month: null, status: 'completed' })).toBe('/activities?year=2026&committee=5&status=completed');
        expect(scheduleUrl({})).toBe('/activities');
    });
});

describe('isOwn', () => {
    it('lo propio trae su avance; lo ajeno, solo los siete datos', () => {
        const own: OwnActivity = { ...shared, committeeId: 3, type: 'Salida', progress: 50, budget: null, fromPlan: true };

        expect(isOwn(own)).toBe(true);
        expect(isOwn(shared)).toBe(false);
    });
});

describe('dayParts', () => {
    it('dice el día, el día de la semana y la hora, en hora de Colombia', () => {
        const parts = dayParts('2026-08-18T19:00:00-05:00');

        expect(parts.day).toBe('18');
        expect(parts.weekday).toContain('mar');
        expect(parts.time.replace(/\s/g, ' ')).toContain('7:00');
    });
});

describe('los estados', () => {
    it('se dicen con las palabras de la congregación', () => {
        expect(STATUS_INFO.in_progress.label).toBe('En camino');
        expect(STATUS_INFO.pending.label).toBe('Esperando aprobación');
    });
});

describe('dateLimits del plan', () => {
    it('para el plan del año siguiente, desde el 1 de enero hasta el 31 de diciembre de ese año', () => {
        expect(dateLimits(new Date('2026-12-01T10:00:00-05:00'), 2027)).toEqual({ min: '2027-01-01', max: '2027-12-31' });
    });

    it('para el plan de este año, desde hoy', () => {
        expect(dateLimits(new Date('2026-03-10T10:00:00-05:00'), 2026)).toEqual({ min: '2026-03-10', max: '2026-12-31' });
    });
});
