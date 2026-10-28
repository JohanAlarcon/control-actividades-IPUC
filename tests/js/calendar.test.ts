import { cellTone, initialDay, markerTone, monthWeeks } from '@/lib/calendar';
import type { SharedActivity } from '@/types/schedule';
import { describe, expect, it } from 'vitest';

const activity = (id: number, scheduledAt: string): SharedActivity => ({
    id,
    title: `Actividad ${id}`,
    scheduledAt,
    endsAt: scheduledAt,
    location: null,
    committee: 'Comité de Alabanza',
    status: 'approved',
});

describe('monthWeeks: el mes como en el cronograma de la sede', () => {
    it('febrero de 2026 empieza el lunes 26 de enero y termina el domingo 1 de marzo, como en el papel', () => {
        const weeks = monthWeeks(2026, 2, []);
        const days = weeks.flat();

        expect(weeks).toHaveLength(5);
        expect(weeks.every((week) => week.length === 7)).toBe(true);
        expect(days[0]).toMatchObject({ date: '2026-01-26', day: 26, inMonth: false, weekday: 0 });
        expect(days[6]).toMatchObject({ date: '2026-02-01', day: 1, inMonth: true, weekday: 6 });
        expect(days.at(-1)).toMatchObject({ date: '2026-03-01', inMonth: false, weekday: 6 });
        expect(days.filter((day) => day.inMonth)).toHaveLength(28);
    });

    it('mayo de 2026 empieza un viernes: el lunes de esa semana es el 27 de abril', () => {
        const first = monthWeeks(2026, 5, [])[0];

        expect(first.map((day) => day.day)).toEqual([27, 28, 29, 30, 1, 2, 3]);
        expect(first.map((day) => day.inMonth)).toEqual([false, false, false, false, true, true, true]);
    });

    it('cada actividad cae en su día en hora de Colombia, ordenada por hora', () => {
        const noche = activity(1, '2026-05-08T00:30:00Z');
        const tarde = activity(2, '2026-05-07T22:00:00Z');
        const dia = monthWeeks(2026, 5, [noche, tarde])
            .flat()
            .find((day) => day.date === '2026-05-07');

        expect(dia?.activities.map((a) => a.id)).toEqual([2, 1]);
    });
});

describe('los colores de la casilla', () => {
    const [week] = monthWeeks(2026, 6, []);

    it('alternan blanco y azul claro, el domingo va en crema y lo de otro mes en gris', () => {
        expect(week.map(cellTone)).toEqual(['bg-white', 'bg-celda-azul', 'bg-white', 'bg-celda-azul', 'bg-white', 'bg-celda-azul', 'bg-celda-domingo']);
        expect(cellTone({ ...week[0], inMonth: false })).toBe('bg-celda-fuera');
    });

    it('la marca de cada actividad: rojo el día especial, naranja lo del distrito o nacional', () => {
        expect(markerTone({ highlight: 'special_day' })).toBe('bg-especial');
        expect(markerTone({ highlight: 'district' })).toBe('bg-cronograma-naranja');
        expect(markerTone({ highlight: 'national' })).toBe('bg-cronograma-naranja');
        expect(markerTone({ highlight: null })).toBe('bg-cronograma');
    });

    it('lo de un cronograma interno lleva su verde, aunque tenga otra marca (RF-119)', () => {
        expect(markerTone({ highlight: null, internal: true })).toBe('bg-interno');
        expect(markerTone({ highlight: 'special_day', internal: true })).toBe('bg-interno');
    });
});

describe('initialDay: el día que se abre al llegar al mes', () => {
    const weeks = monthWeeks(2026, 5, [activity(1, '2026-05-14T00:00:00Z')]);

    it('hoy, si es de ese mes', () => {
        expect(initialDay(weeks, '2026-05-20')).toBe('2026-05-20');
    });

    it('si no, el primer día con actividades; y si no hay, el primero del mes', () => {
        expect(initialDay(weeks, '2026-10-28')).toBe('2026-05-13');
        expect(initialDay(monthWeeks(2026, 6, []), '2026-10-28')).toBe('2026-06-01');
    });
});
