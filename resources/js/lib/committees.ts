import { ROLE_LABELS } from '@/lib/roles';

const LEVELS: Record<number, string> = { 1: 'Comité', 2: 'Sub-comité', 3: 'Equipo' };

interface Countable {
    depth: number;
    children?: Countable[];
}

export function levelOf(depth: number): string {
    return LEVELS[depth] ?? LEVELS[1];
}

/** Un comité lo encabeza su presidente; un sub-comité o un equipo, su líder (RF-013). */
export function responsibleTitle(depth: number): string {
    return depth === 1 ? ROLE_LABELS.president : ROLE_LABELS.leader;
}

export function childrenTitle(depth: number): string {
    return depth === 1 ? 'Sub-comités' : 'Equipos';
}

/** RF-012 y CL-01: sin hijos, la ficha lo dice expresamente en vez de mostrar una sección vacía. */
export function withoutChildrenText(depth: number): string {
    if (depth === 1) return 'Este comité no tiene sub-comités: su presidente coordina directamente a sus miembros.';
    if (depth === 2) return 'Este sub-comité no tiene equipos: su líder coordina directamente a sus miembros.';

    return 'Un equipo es el último nivel: su líder coordina directamente a sus miembros.';
}

/** Todos los nodos del árbol en una lista, cada uno seguido de lo que cuelga de él. */
export function flattenTree<T extends Countable>(nodes: T[]): T[] {
    return nodes.flatMap((node) => [node, ...flattenTree((node.children ?? []) as T[])]);
}

/** Cuántos hay de cada nivel: «6 comités · 4 sub-comités · 1 equipo». */
export function structureSummary(nodes: Countable[]): string {
    const all = flattenTree(nodes);
    const count = (depth: number) => all.filter((node) => node.depth === depth).length;
    const parts: [number, string, string][] = [
        [count(1), 'comité', 'comités'],
        [count(2), 'sub-comité', 'sub-comités'],
        [count(3), 'equipo', 'equipos'],
    ];

    return parts
        .filter(([total]) => total > 0)
        .map(([total, one, many]) => `${total} ${total === 1 ? one : many}`)
        .join(' · ');
}
