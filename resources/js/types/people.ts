export type Gender = 'f' | 'm';
export type MaritalStatus = 'single' | 'married' | 'widowed' | 'separated' | 'common_law';
export type ConsentMeans = 'signature' | 'verbal' | 'message';
export type CommitmentLevel = 'growing' | 'stable' | 'drifting';
export type AgeMode = 'date' | 'age';

/** Un miembro en la lista: lo que muestra su tarjeta y lo que le falta a su ficha. */
export interface MemberCard {
    id: number;
    name: string;
    age: number | null;
    committee: string | null;
    gender: Gender;
    maritalStatus: MaritalStatus;
    baptized: boolean;
    phone: string | null;
    neighborhood: string | null;
    dataConsent: boolean;
    profilePending: boolean;
}

export interface MemberFilters {
    committee: number | null;
    gender: Gender | null;
    marital: MaritalStatus | null;
    baptized: 'yes' | 'no' | null;
    age: string | null;
}

/** La ficha completa de un miembro, como la corrige el formulario. */
export interface MemberDetail {
    id: number;
    name: string;
    ageMode: AgeMode;
    birthDate: string | null;
    ageReported: number | null;
    age: number | null;
    gender: Gender;
    maritalStatus: MaritalStatus;
    adultConfirmed: boolean;
    baptized: boolean;
    baptizedAt: string | null;
    joinedAt: string | null;
    committeeId: number | null;
    phone: string | null;
    neighborhood: string | null;
    dataConsent: boolean;
    dataConsentAt: string | null;
    dataConsentMeans: ConsentMeans | null;
}

/** El compromiso, calculado por el servidor con las señales que lo determinaron (RF-043). */
export interface Commitment {
    level: CommitmentLevel;
    recentContacts: number;
    daysSinceContact: number;
}

export interface SympathizerCard {
    id: number;
    name: string;
    age: number | null;
    phone: string | null;
    neighborhood: string | null;
    stage: number;
    commitment: Commitment;
    promotionPending: boolean;
    convertedAt: string | null;
}

export interface SympathizerDetail extends SympathizerCard {
    gender: Gender;
    arrivalSource: string;
    invitedBy: string | null;
    firstContactAt: string;
    dataConsent: boolean;
}

export interface StageItem {
    stage: number;
    reachedAt: string;
    recordedBy: string | null;
}

export interface VisitItem {
    id: number;
    visitedAt: string;
    type: 'visit' | 'call' | 'study' | 'service';
    by: string | null;
    note: string | null;
}

export interface Thresholds {
    recentDays: number;
    growingContacts: number;
    growingDays: number;
    driftingDays: number;
}
