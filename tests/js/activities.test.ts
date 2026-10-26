import { ACTIVITY_TYPES, dateLimits } from '@/lib/activities';
import { describe, expect, it } from 'vitest';

describe('dateLimits', () => {
    it('deja escoger de hoy al 31 de diciembre', () => {
        expect(dateLimits(new Date('2026-10-26T15:00:00-05:00'))).toEqual({ min: '2026-10-26', max: '2026-12-31' });
    });

    it('cuenta el día en Colombia: el 31 a las 11:30 p. m. todavía es este año', () => {
        expect(dateLimits(new Date('2026-12-31T23:30:00-05:00'))).toEqual({ min: '2026-12-31', max: '2026-12-31' });
    });
});

describe('ACTIVITY_TYPES', () => {
    it('ofrece las ocho clases que acepta el servidor', () => {
        expect(ACTIVITY_TYPES.map((type) => type.value).sort()).toEqual(['campaign', 'convention', 'event', 'meeting', 'outing', 'rehearsal', 'service', 'visit']);
    });
});
