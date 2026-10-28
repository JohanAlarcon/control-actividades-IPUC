import { ADULT_ONLY, ageText, daysAgo, functionLabel, maritalLabel, memberMatches, membersUrl, STAGES } from '@/lib/people';
import { describe, expect, it } from 'vitest';

describe('maritalLabel', () => {
    it('dice el estado civil como corresponde a la persona', () => {
        expect(maritalLabel('married', 'f')).toBe('Casada');
        expect(maritalLabel('widowed', 'm')).toBe('Viudo');
        expect(maritalLabel('common_law', 'f')).toBe('En unión libre');
    });

    it('sin saber si es hombre o mujer, da las dos formas', () => {
        expect(maritalLabel('single')).toBe('Soltero o soltera');
    });
});

describe('ageText', () => {
    it('sin fecha ni edad anotada dice que falta, en vez de mostrar cero', () => {
        expect(ageText(null)).toBe('Edad por completar');
        expect(ageText(42)).toBe('42 años');
        expect(ageText(1)).toBe('1 año');
    });
});

describe('daysAgo', () => {
    it('dice cuánto hace, en palabras', () => {
        expect(daysAgo(0)).toBe('hoy');
        expect(daysAgo(1)).toBe('ayer');
        expect(daysAgo(12)).toBe('hace 12 días');
    });
});

describe('membersUrl', () => {
    it('lleva los filtros en la dirección, sin los vacíos', () => {
        expect(membersUrl({ committee: 4, gender: null, baptized: 'yes' })).toBe('/members?committee=4&baptized=yes');
        expect(membersUrl({})).toBe('/members');
    });
});

describe('STAGES', () => {
    it('son las cinco etapas, en orden, con el vocabulario de la IPUC', () => {
        expect(STAGES).toHaveLength(5);
        expect(STAGES[3]).toBe('Se bautizó en el nombre de Jesucristo');
    });
});

describe('memberMatches: buscar a un hermano por nombre o por barrio (HU-10, escenario 4)', () => {
    const hermano = { name: 'Hermano Julián Hernández', neighborhood: 'Picaleña' };

    it('no distingue mayúsculas ni tildes', () => {
        expect(memberMatches(hermano, 'hernandez')).toBe(true);
        expect(memberMatches({ name: 'Hermana Rosa Niño', neighborhood: null }, 'NIÑO')).toBe(true);
    });

    it('encuentra por el barrio y por parte del texto', () => {
        expect(memberMatches(hermano, 'picalena')).toBe(true);
        expect(memberMatches(hermano, 'juli')).toBe(true);
    });

    it('cada palabra tiene que estar', () => {
        expect(memberMatches(hermano, 'julian salado')).toBe(false);
    });
});

describe('el estado civil «divorciado» (RF-031)', () => {
    it('se dice como corresponde y es solo de mayores de edad', () => {
        expect(maritalLabel('divorced', 'f')).toBe('Divorciada');
        expect(maritalLabel('divorced', 'm')).toBe('Divorciado');
        expect(ADULT_ONLY).toContain('divorced');
    });
});

describe('functionLabel: lo que hace la persona en la lista de un comité (RF-084)', () => {
    it('dice el cargo de la directiva, y si es hombre o mujer', () => {
        expect(functionLabel({ role: 'board', boardPosition: 'president' }, 'f')).toBe('Presidenta');
        expect(functionLabel({ role: 'board', boardPosition: 'vocal' }, 'm')).toBe('Vocal');
    });

    it('las demás funciones, sin cargo', () => {
        expect(functionLabel({ role: 'teacher', boardPosition: null }, 'f')).toBe('Maestra');
        expect(functionLabel({ role: 'member', boardPosition: null }, 'm')).toBe('Integrante');
    });
});
