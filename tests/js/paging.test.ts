import { PAGE_SIZE, pageOf } from '@/lib/paging';
import { describe, expect, it } from 'vitest';

const lista = Array.from({ length: 45 }, (_, i) => i + 1);

describe('pageOf: las páginas de una lista larga', () => {
    it('trae veinte por página y dice cuántas páginas hay', () => {
        const primera = pageOf(lista, 1);

        expect(primera.items).toHaveLength(PAGE_SIZE);
        expect(primera.items[0]).toBe(1);
        expect(primera.pages).toBe(3);
    });

    it('la última página trae lo que sobra', () => {
        expect(pageOf(lista, 3).items).toEqual([41, 42, 43, 44, 45]);
    });

    it('una página que ya no existe se corrige a la última', () => {
        const despuesDeBuscar = pageOf(lista.slice(0, 8), 3);

        expect(despuesDeBuscar.page).toBe(1);
        expect(despuesDeBuscar.items).toHaveLength(8);
    });

    it('una lista vacía tiene una sola página, vacía', () => {
        expect(pageOf([], 1)).toEqual({ items: [], page: 1, pages: 1 });
    });
});
