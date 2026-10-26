import type { Role } from '@/lib/roles';
import type { Flash } from '@/types/accounts';

export interface SharedRole {
    id: number;
    committeeId: number;
    committeeName: string | null;
    role: Role;
}

/** Lo que el servidor manda en toda pantalla (contracts/shared-props.md). Los dos últimos llegan aparte, después. */
export type SharedProps = {
    auth: {
        user: { id: number; name: string; username: string; isPastor: boolean } | null;
        roles: SharedRole[];
    };
    scope: {
        activeRole: SharedRole | null;
        label: string | null;
        mustChoose: boolean;
        canSwitch: boolean;
    };
    flash: Flash;
    pendingCount?: number;
    scopeHidden?: { committees: number };
};
