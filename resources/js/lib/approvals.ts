import type { ApprovalItem, ApprovalType } from '@/types/approvals';

const PLURALS: Record<ApprovalType, { article: string; noun: string }> = {
    activity: { article: 'las', noun: 'actividades nuevas' },
    expense: { article: 'los', noun: 'gastos' },
    task: { article: 'las', noun: 'tareas' },
    date_change: { article: 'los', noun: 'cambios de fecha' },
    new_member: { article: 'los', noun: 'miembros nuevos' },
};

/** «Aprobar los 3 gastos»: el botón de cada grupo que el pastor puede aprobar de una vez (RF-022). */
export function bulkLabel(type: ApprovalType, count: number): string {
    const { article, noun } = PLURALS[type];

    return `Aprobar ${article} ${count} ${noun}`;
}

/** Las solicitudes agrupadas por clase, solo los grupos de dos o más: las que vale la pena aprobar juntas. */
export function groupsForBulk(items: ApprovalItem[]): { type: ApprovalType; ids: number[] }[] {
    const groups = new Map<ApprovalType, number[]>();

    items.forEach((item) => groups.set(item.type, [...(groups.get(item.type) ?? []), item.id]));

    return [...groups].filter(([, ids]) => ids.length > 1).map(([type, ids]) => ({ type, ids }));
}
