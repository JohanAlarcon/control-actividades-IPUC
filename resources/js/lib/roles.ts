export type Role = 'president' | 'secretary' | 'treasurer' | 'leader';

export const ROLE_LABELS: Record<Role, string> = {
    president: 'Presidente',
    secretary: 'Secretario',
    treasurer: 'Tesorero',
    leader: 'Líder',
};

const FEMININE_LABELS: Record<Role, string> = {
    president: 'Presidenta',
    secretary: 'Secretaria',
    treasurer: 'Tesorera',
    leader: 'Líder',
};

/**
 * El cargo dicho como se le dice a la persona: «Presidenta» si el pastor la registró como
 * hermana. Sin el trato en el nombre, la forma general (decisión del autor, 2026-10-26).
 */
export function roleLabelFor(role: Role, name?: string | null): string {
    return /^(hermana|pastora)\s/i.test((name ?? '').trim()) ? FEMININE_LABELS[role] : ROLE_LABELS[role];
}

/** La misma regla del servidor: un comité tiene presidente, secretario y tesorero; un sub-comité, líder. */
export function rolesForDepth(depth: number): Role[] {
    return depth === 1 ? ['president', 'secretary', 'treasurer'] : ['leader'];
}

/** Quien decide solicitudes: el pastor y quien actúa como presidente (RF-016, RF-017). La misma regla de ApprovalPolicy. */
export function decidesApprovals(isPastor: boolean | undefined, activeRole: Role | undefined): boolean {
    return Boolean(isPastor) || activeRole === 'president';
}

/** Quien propone actividades: el líder y el presidente, con el cargo con el que actúan (HU-05). La misma regla de ActivityPolicy. */
export function canPropose(activeRole: Role | undefined): boolean {
    return activeRole === 'president' || activeRole === 'leader';
}
