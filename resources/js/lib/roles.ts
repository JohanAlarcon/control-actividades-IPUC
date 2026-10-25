export type Role = 'president' | 'secretary' | 'treasurer' | 'leader';

export const ROLE_LABELS: Record<Role, string> = {
    president: 'Presidente',
    secretary: 'Secretario',
    treasurer: 'Tesorero',
    leader: 'Líder',
};

/** La misma regla del servidor: un comité tiene presidente, secretario y tesorero; un sub-comité o un equipo, líder. */
export function rolesForDepth(depth: number): Role[] {
    return depth === 1 ? ['president', 'secretary', 'treasurer'] : ['leader'];
}
