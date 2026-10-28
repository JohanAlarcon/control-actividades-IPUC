import { ROLE_LABELS, type Role } from '@/lib/roles';

const LEVELS: Record<number, string> = { 1: 'Comité', 2: 'Sub-comité' };

interface Countable {
    depth: number;
    children?: Countable[];
}

export function levelOf(depth: number): string {
    return LEVELS[depth] ?? LEVELS[1];
}

/** Un comité lo encabeza su presidente; un sub-comité, su líder (RF-013). */
export function responsibleRole(depth: number): Role {
    return depth === 1 ? 'president' : 'leader';
}

export function responsibleTitle(depth: number): string {
    return ROLE_LABELS[responsibleRole(depth)];
}

export function childrenTitle(): string {
    return 'Sub-comités';
}

/** RF-012 y CL-01: sin hijos, la ficha lo dice expresamente en vez de mostrar una sección vacía. */
export function withoutChildrenText(depth: number): string {
    if (depth === 1) return 'Este comité no tiene sub-comités: su presidente coordina directamente a sus miembros.';

    return 'Un sub-comité es el último nivel: su líder coordina directamente a sus miembros.';
}

/** Todos los nodos del árbol en una lista, cada uno seguido de lo que cuelga de él. */
export function flattenTree<T extends Countable>(nodes: T[]): T[] {
    return nodes.flatMap((node) => [node, ...flattenTree((node.children ?? []) as T[])]);
}

/** Cuántos hay de cada nivel: «15 comités · 6 sub-comités». */
export function structureSummary(nodes: Countable[]): string {
    const all = flattenTree(nodes);
    const count = (depth: number) => all.filter((node) => node.depth === depth).length;
    const parts: [number, string, string][] = [
        [count(1), 'comité', 'comités'],
        [count(2), 'sub-comité', 'sub-comités'],
    ];

    return parts
        .filter(([total]) => total > 0)
        .map(([total, one, many]) => `${total} ${total === 1 ? one : many}`)
        .join(' · ');
}

/**
 * Las iniciales de un comité sin logo (HU-26 escenario 3): «Damas Dorcas» da «DD» y «Comité de
 * Alabanza» da «A», sin la palabra «Comité» ni los «de». Una sigla como «BIS» va entera.
 */
export function committeeInitials(name: string): string {
    const words = name
        .replace(/^(comité|sub-comité|subcomité)\s+/i, '')
        .split(/\s+/)
        .filter((word) => word && !/^(de|del|la|las|los|el|su|sus|y|e)$/i.test(word));

    if (words.length === 1 && /^[A-ZÁÉÍÓÚÑ]{2,5}$/.test(words[0])) {
        return words[0];
    }

    return words
        .slice(0, 2)
        .map((word) => word[0])
        .join('')
        .toUpperCase();
}
