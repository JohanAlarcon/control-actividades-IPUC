import { bulkLabel, groupsForBulk } from '@/lib/approvals';
import type { ApprovalItem, ApprovalType } from '@/types/approvals';
import { describe, expect, it } from 'vitest';

const item = (id: number, type: ApprovalType) => ({ id, type }) as ApprovalItem;

describe('bulkLabel', () => {
    it('dice cuántas y de qué clase, con el artículo que corresponde', () => {
        expect(bulkLabel('expense', 3)).toBe('Aprobar los 3 gastos');
        expect(bulkLabel('activity', 2)).toBe('Aprobar las 2 actividades nuevas');
        expect(bulkLabel('date_change', 4)).toBe('Aprobar los 4 cambios de fecha');
    });
});

describe('groupsForBulk', () => {
    it('agrupa por clase y deja solo los grupos de dos o más', () => {
        const items = [item(1, 'expense'), item(2, 'activity'), item(3, 'expense'), item(4, 'task'), item(5, 'expense')];

        expect(groupsForBulk(items)).toEqual([{ type: 'expense', ids: [1, 3, 5] }]);
    });

    it('sin repetidas no hay nada para aprobar en lote', () => {
        expect(groupsForBulk([item(1, 'expense'), item(2, 'activity')])).toEqual([]);
    });
});
