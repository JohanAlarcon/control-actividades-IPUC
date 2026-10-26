import { activityDate, groupThousands, money, shortDate, waitingSince } from '@/lib/format';
import { describe, expect, it } from 'vitest';

/** Intl separa con espacios que no se parten; para comparar, basta un espacio normal. */
const plain = (text: string) => text.replace(/\s/g, ' ');

describe('money', () => {
    it('dice los pesos sin centavos y con punto de miles', () => {
        expect(plain(money(850000))).toBe('$ 850.000');
        expect(plain(money(9800000))).toBe('$ 9.800.000');
    });
});

describe('waitingSince', () => {
    const now = new Date('2026-10-26T15:00:00-05:00');

    it('cuenta días del calendario: lo que llegó anoche ya es de ayer', () => {
        expect(waitingSince('2026-10-26T08:00:00-05:00', now)).toBe('desde hoy');
        expect(waitingSince('2026-10-25T23:30:00-05:00', now)).toBe('desde ayer');
        expect(waitingSince('2026-10-22T10:00:00-05:00', now)).toBe('desde hace 4 días');
    });

    it('usa la hora de Colombia aunque el dato venga en otra zona', () => {
        expect(waitingSince('2026-10-26T03:00:00Z', now)).toBe('desde ayer');
    });

    it('pasadas dos semanas, dice la fecha', () => {
        expect(waitingSince('2026-09-22T10:00:00-05:00', now)).toBe('desde el 22 de septiembre');
    });
});

describe('fechas', () => {
    it('dice el día y el mes', () => {
        expect(shortDate('2026-10-22T10:00:00-05:00')).toBe('22 de octubre');
    });

    it('dice cuándo es una actividad: día de la semana, fecha y hora', () => {
        const text = plain(activityDate('2026-11-07T19:00:00-05:00'));

        expect(text).toContain('sábado');
        expect(text).toContain('7 de noviembre');
        expect(text).toContain('7:00');
    });
});

describe('groupThousands', () => {
    it('pone los puntos de mil mientras se escribe', () => {
        expect(groupThousands('350000')).toBe('350.000');
        expect(groupThousands('3500005')).toBe('3.500.005');
        expect(groupThousands('12')).toBe('12');
    });

    it('quita lo que no es número, aunque venga pegado con signo o con los puntos viejos', () => {
        expect(groupThousands('$ 9.800.000')).toBe('9.800.000');
        expect(groupThousands('350.0005')).toBe('3.500.005');
        expect(groupThousands('mucho')).toBe('');
    });

    it('no deja ceros adelante, pero sí un cero solo', () => {
        expect(groupThousands('007')).toBe('7');
        expect(groupThousands('0')).toBe('0');
    });
});
