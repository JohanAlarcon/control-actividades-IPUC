import { byDay, responsibleOf, shareText, whatsappUrl } from '@/lib/share';
import type { PrintActivity } from '@/types/print';
import { describe, expect, it } from 'vitest';

const ensayo: PrintActivity = {
    id: 1,
    title: 'Ensayo general',
    scheduledAt: '2026-11-07T19:00:00-05:00',
    endsAt: '2026-11-07T21:00:00-05:00',
    location: 'Templo central',
    committee: 'Comité de Alabanza',
    responsible: 'La directora del coro',
};
const culto: PrintActivity = {
    id: 2,
    title: 'Culto de jóvenes',
    scheduledAt: '2026-11-08T17:00:00-05:00',
    endsAt: '2026-11-08T19:00:00-05:00',
    location: 'Salón 2',
    committee: 'Sub-comité de Sonido',
    responsible: null,
};
const periodo = { from: '2026-11-01', to: '2026-11-30' };
/** La hora lleva espacios que no se parten; aquí se comparan como espacios normales. */
const plain = (text: string) => text.replace(/[  ]/g, ' ');

describe('el resumen para WhatsApp', () => {
    const texto = plain(shareText('IPUC La Esmeralda', 'Comité de Alabanza', periodo, [ensayo, culto]));

    it('trae el comité, la sede y las fechas', () => {
        expect(texto.split('\n').slice(0, 2)).toEqual(['*Comité de Alabanza*', 'IPUC La Esmeralda. Actividades del 1 de noviembre al 30 de noviembre de 2026.']);
    });

    it('trae la fecha, la hora, el lugar y el responsable de cada actividad', () => {
        expect(texto).toContain('*Sábado, 7 de noviembre*\n• 7:00 a 9:00 p. m. · Ensayo general\nLugar: Templo central\nResponsable: La directora del coro');
        expect(texto).toContain('*Domingo, 8 de noviembre*\n• 5:00 a 7:00 p. m. · Culto de jóvenes\nLugar: Salón 2\nResponsable: Sub-comité de Sonido');
    });

    it('sin actividades lo dice, en vez de mandar solo el título', () => {
        expect(shareText('IPUC La Esmeralda', 'Comité de Alabanza', periodo, [])).toContain('No hay actividades aprobadas en estas fechas.');
    });

    it('abre WhatsApp con el texto escrito y sin destinatario: la persona escoge a quién', () => {
        const url = whatsappUrl('*Hola*\nLugar: Templo & salón');

        expect(url).toBe('https://wa.me/?text=*Hola*%0ALugar%3A%20Templo%20%26%20sal%C3%B3n');
        expect(decodeURIComponent(url.split('text=')[1])).toBe('*Hola*\nLugar: Templo & salón');
    });
});

describe('quién está a cargo', () => {
    it('la persona que se escribió; con su sub-comité si no es el que se imprime', () => {
        expect(responsibleOf(ensayo, 'Comité de Alabanza')).toBe('La directora del coro');
        expect(responsibleOf({ ...ensayo, committee: 'Sub-comité de Coro' }, 'Comité de Alabanza')).toBe('La directora del coro (Sub-comité de Coro)');
    });

    it('si no se escribió nadie, el comité', () => {
        expect(responsibleOf(culto, 'Comité de Alabanza')).toBe('Sub-comité de Sonido');
    });
});

describe('las actividades por día', () => {
    it('cuenta el día en Colombia: las diez de la noche siguen siendo el mismo día', () => {
        const tarde = { ...ensayo, id: 3, scheduledAt: '2026-11-08T03:00:00Z', endsAt: '2026-11-08T04:00:00Z' };

        expect(byDay([ensayo, tarde, culto]).map((day) => [day.date, day.activities.map((activity) => activity.id)])).toEqual([
            ['2026-11-07', [1, 3]],
            ['2026-11-08', [2]],
        ]);
    });
});
