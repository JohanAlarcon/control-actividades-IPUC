import { clockTime, activityDate, activityWhen, groupThousands, money, shortDate, shortTimeRange, timeRange, waitingSince } from '@/lib/format';
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

    it('dice de qué hora a qué hora es, en hora de Colombia', () => {
        expect(plain(timeRange('2026-05-07T17:00:00-05:00', '2026-05-07T18:00:00-05:00'))).toBe('5:00 a 6:00 p. m.');
        expect(plain(timeRange('2026-05-07T15:00:00Z', '2026-05-07T23:00:00Z'))).toBe('10:00 a. m. a 6:00 p. m.');
    });

    it('el «p. m.» no se parte en dos renglones', () => {
        expect(timeRange('2026-05-07T17:00:00-05:00', '2026-05-07T18:00:00-05:00')).toMatch(/p\.\u00a0m\.$/);
    });

    it('en la casilla del mes, las horas en punto van en corto', () => {
        expect(plain(shortTimeRange('2026-05-07T17:00:00-05:00', '2026-05-07T18:00:00-05:00'))).toBe('5 a 6 p. m.');
        expect(plain(shortTimeRange('2026-05-07T17:00:00-05:00', '2026-05-07T18:30:00-05:00'))).toBe('5 a 6:30 p. m.');
    });

    it('dice el día y las dos horas', () => {
        expect(plain(activityWhen('2026-05-07T17:00:00-05:00', '2026-05-07T18:00:00-05:00'))).toBe('jueves, 7 de mayo, de 5:00 a 6:00 p. m.');
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

describe('clockTime: la hora para el reloj de un formulario', () => {
    it('da la hora de Colombia en veinticuatro horas', () => {
        expect(clockTime('2026-04-19T00:00:00Z')).toBe('19:00');
    });

    it('la medianoche es 00, no 24', () => {
        expect(clockTime('2026-04-19T05:30:00Z')).toBe('00:30');
    });
});
