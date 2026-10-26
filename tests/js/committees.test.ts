import { flattenTree, levelOf, structureSummary, withoutChildrenText } from '@/lib/committees';
import { describe, expect, it } from 'vitest';

const arbol = [
    {
        depth: 1,
        children: [
            { depth: 2, children: [{ depth: 3, children: [] }] },
            { depth: 2, children: [] },
        ],
    },
    { depth: 1, children: [] },
];

describe('structureSummary: el resumen bajo el título de Comités', () => {
    it('cuenta cada nivel con su nombre', () => {
        expect(structureSummary(arbol)).toBe('2 comités · 2 sub-comités · 1 equipo');
    });

    it('usa el singular cuando es uno', () => {
        expect(structureSummary([{ depth: 1, children: [] }])).toBe('1 comité');
    });

    it('no menciona los niveles que no hay', () => {
        expect(structureSummary([{ depth: 2, children: [] }])).toBe('1 sub-comité');
    });

    it('sin comités, no dice nada', () => {
        expect(structureSummary([])).toBe('');
    });
});

describe('flattenTree: el orden en que se recorre el árbol', () => {
    it('pone cada comité seguido de lo que cuelga de él', () => {
        expect(flattenTree(arbol).map((nodo) => nodo.depth)).toEqual([1, 2, 3, 2, 1]);
    });
});

describe('los textos de cada nivel', () => {
    it('cada nivel tiene su nombre', () => {
        expect([1, 2, 3].map(levelOf)).toEqual(['Comité', 'Sub-comité', 'Equipo']);
    });

    it('un nodo sin hijos explica quién coordina, en vez de una sección vacía (CL-01)', () => {
        expect(withoutChildrenText(1)).toContain('su presidente coordina directamente');
        expect(withoutChildrenText(2)).toContain('su líder coordina directamente');
        expect(withoutChildrenText(3)).toContain('último nivel');
    });
});
