import { decidesApprovals, roleLabelFor, rolesForDepth } from '@/lib/roles';
import { describe, expect, it } from 'vitest';

describe('roleLabelFor: el cargo dicho como se le dice a la persona', () => {
    it('a una hermana le dice Presidenta, Secretaria y Tesorera', () => {
        expect(roleLabelFor('president', 'Hermana Blanca Cárdenas')).toBe('Presidenta');
        expect(roleLabelFor('secretary', 'Hermana Luz Mary Quintero')).toBe('Secretaria');
        expect(roleLabelFor('treasurer', 'Hermana Rosa Díaz')).toBe('Tesorera');
    });

    it('a un hermano le dice Presidente, Secretario y Tesorero', () => {
        expect(roleLabelFor('president', 'Hermano Rigoberto Páez')).toBe('Presidente');
        expect(roleLabelFor('secretary', 'Hermano Jairo Betancur')).toBe('Secretario');
        expect(roleLabelFor('treasurer', 'Hermano Jairo Betancur')).toBe('Tesorero');
    });

    it('líder se dice igual para los dos', () => {
        expect(roleLabelFor('leader', 'Hermana Omaira Lozano')).toBe('Líder');
        expect(roleLabelFor('leader', 'Hermano Andrés Molina')).toBe('Líder');
    });

    it('sin el trato en el nombre, o sin nombre, usa la forma general', () => {
        expect(roleLabelFor('president', 'Blanca Cárdenas')).toBe('Presidente');
        expect(roleLabelFor('president', null)).toBe('Presidente');
        expect(roleLabelFor('president')).toBe('Presidente');
    });

    it('no se confunde con mayúsculas ni con espacios de más', () => {
        expect(roleLabelFor('president', '  HERMANA Blanca Cárdenas')).toBe('Presidenta');
        expect(roleLabelFor('president', 'hermana blanca cárdenas')).toBe('Presidenta');
    });

    it('una palabra que solo empieza igual no cuenta como el trato', () => {
        expect(roleLabelFor('president', 'Hermanita Rosa')).toBe('Presidente');
    });
});

describe('rolesForDepth: qué cargos tiene cada nivel (RF-013)', () => {
    it('un comité tiene presidente, secretario y tesorero', () => {
        expect(rolesForDepth(1)).toEqual(['president', 'secretary', 'treasurer']);
    });

    it('un sub-comité y un equipo tienen líder', () => {
        expect(rolesForDepth(2)).toEqual(['leader']);
        expect(rolesForDepth(3)).toEqual(['leader']);
    });
});

describe('decidesApprovals', () => {
    it('deciden el pastor y quien actúa como presidente; nadie más', () => {
        expect(decidesApprovals(true, undefined)).toBe(true);
        expect(decidesApprovals(false, 'president')).toBe(true);
        expect(decidesApprovals(false, 'secretary')).toBe(false);
        expect(decidesApprovals(false, 'leader')).toBe(false);
        expect(decidesApprovals(undefined, undefined)).toBe(false);
    });
});
