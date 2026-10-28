import type { Role } from '@/lib/roles';
import type { Flash } from '@/types/accounts';

export interface SharedRole {
    id: number;
    committeeId: number;
    committeeName: string | null;
    role: Role;
}

/** Lo que el servidor manda en toda pantalla (contracts/shared-props.md). Los tres últimos llegan aparte, después. */
/** El lema del año en curso de la sede, con su cita (RF-082). */
export interface SiteMotto {
    year: number;
    text: string;
    citation: string | null;
}

/** El nombre de la sede y su lema del año (RF-081). Viaja también en la entrada, sin sesión. */
export interface SiteIdentity {
    name: string;
    city: string | null;
    motto: SiteMotto | null;
}

export type SharedProps = {
    site: SiteIdentity | null;
    auth: {
        user: { id: number; name: string; username: string; isPastor: boolean } | null;
        roles: SharedRole[];
        /** Si el cargo activo acompaña a simpatizantes: todo cargo y el pastor (RF-040, D-34). */
        followsSympathizers: boolean;
    };
    scope: {
        activeRole: SharedRole | null;
        label: string | null;
        mustChoose: boolean;
        canSwitch: boolean;
    };
    flash: Flash;
    pendingCount?: number;
    pendingSince?: string | null;
    scopeHidden?: { committees: number; members: number };
};
