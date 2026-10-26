import { ROLE_LABELS } from '@/lib/roles';

const LEVELS: Record<number, string> = { 1: 'Comité', 2: 'Sub-comité', 3: 'Equipo' };

export function levelOf(depth: number): string {
    return LEVELS[depth] ?? LEVELS[1];
}

/** Un comité lo encabeza su presidente; un sub-comité o un equipo, su líder (RF-013). */
export function responsibleTitle(depth: number): string {
    return depth === 1 ? ROLE_LABELS.president : ROLE_LABELS.leader;
}

/** RF-012 y CL-01: sin hijos, la ficha lo dice expresamente en vez de mostrar una sección vacía. */
export function withoutChildrenText(depth: number): string {
    if (depth === 1) return 'Este comité no tiene sub-comités: su presidente coordina directamente a sus miembros.';
    if (depth === 2) return 'Este sub-comité no tiene equipos: su líder coordina directamente a sus miembros.';

    return 'Un equipo es el último nivel: su líder coordina directamente a sus miembros.';
}

export function childrenText(depth: number, count: number): string {
    const kind = depth === 1 ? ['sub-comité', 'sub-comités'] : ['equipo', 'equipos'];

    return `Tiene ${count} ${count === 1 ? kind[0] : kind[1]}:`;
}
