import type { Role } from '@/lib/roles';

export interface Account {
    id: number;
    name: string;
    username: string;
    isPastor: boolean;
    active: boolean;
    mustChangePassword: boolean;
    roles: { id: number; role: Role; committeeName: string | null }[];
}

export interface EditableAccount {
    id: number;
    name: string;
    username: string;
    active: boolean;
    isPastor: boolean;
    mustChangePassword: boolean;
}

export interface CommitteeOption {
    id: number;
    name: string;
    depth: number;
    active: boolean;
}

export interface CurrentRole {
    id: number;
    role: Role;
    committeeId: number;
}

export interface Credentials {
    name: string;
    username: string;
    password: string;
}

export interface Flash {
    notice: string | null;
    temporaryPassword: Credentials | null;
}
