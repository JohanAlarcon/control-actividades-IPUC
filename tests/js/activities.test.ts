import { dateLimits, usesText } from '@/lib/activities';
import { describe, expect, it } from 'vitest';

describe('dateLimits', () => {
    it('deja escoger de hoy al 31 de diciembre', () => {
        expect(dateLimits(new Date('2026-10-26T15:00:00-05:00'))).toEqual({ min: '2026-10-26', max: '2026-12-31' });
    });

    it('cuenta el día en Colombia: el 31 a las 11:30 p. m. todavía es este año', () => {
        expect(dateLimits(new Date('2026-12-31T23:30:00-05:00'))).toEqual({ min: '2026-12-31', max: '2026-12-31' });
    });
});

describe('usesText', () => {
    it('dice cuánto se ha usado una clase, en singular y en plural', () => {
        expect(usesText(0)).toBe('Todavía no se ha usado');
        expect(usesText(1)).toBe('Se ha usado en 1 actividad');
        expect(usesText(12)).toBe('Se ha usado en 12 actividades');
    });
});
