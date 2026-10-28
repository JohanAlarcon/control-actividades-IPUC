import { dateLimits } from '@/lib/activities';
import { periodLabel, dayParts, HIGHLIGHT_INFO, isOwn, lookOf, scheduleUrl, STATUS_INFO } from '@/lib/schedule';
import type { OwnActivity, SharedActivity } from '@/types/schedule';
import { describe, expect, it } from 'vitest';

const shared: SharedActivity = {
    id: 1,
    title: 'Retiro de damas',
    scheduledAt: '2026-08-18T19:00:00-05:00',
    endsAt: '2026-08-18T21:00:00-05:00',
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

const own: OwnActivity = {
    ...shared,
    description: null,
    theme: 'Mujeres de fe',
    highlight: 'special_day',
    committeeId: 3,
    type: 'Salida',
    progress: 50,
    budget: null,
    fromPlan: true,
};

describe('isOwn', () => {
    it('lo propio trae su avance; lo ajeno, solo los seis datos', () => {
        expect(isOwn(own)).toBe(true);
        expect(isOwn(shared)).toBe(false);
    });
});

describe('lookOf: el tema y la marca de color', () => {
    it('lo propio los trae; de lo ajeno no viajan (P-09)', () => {
        expect(lookOf(own)).toEqual({ highlight: 'special_day', theme: 'Mujeres de fe' });
        expect(lookOf(shared)).toEqual({ highlight: null, theme: null });
    });

    it('cada marca se dice con palabras, no solo con el color', () => {
        expect(HIGHLIGHT_INFO.special_day).toEqual({ label: 'Día especial', text: 'text-especial' });
        expect(HIGHLIGHT_INFO.district.text).toBe(HIGHLIGHT_INFO.national.text);
    });
});

describe('dayParts', () => {
    it('dice el día y el día de la semana, en hora de Colombia', () => {
        const parts = dayParts('2026-08-19T00:30:00Z');

        expect(parts.day).toBe('18');
        expect(parts.weekday).toContain('mar');
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

describe('periodLabel: el periodo de un cronograma interno', () => {
    it('dice de qué día a qué día va, con el año al final', () => {
        expect(periodLabel('2026-02-01', '2026-06-30')).toBe('Del 1 de febrero al 30 de junio de 2026');
    });

    it('el primero de enero sigue siendo el primero de enero, en cualquier zona horaria', () => {
        expect(periodLabel('2026-01-01', '2026-12-31')).toBe('Del 1 de enero al 31 de diciembre de 2026');
    });
});
