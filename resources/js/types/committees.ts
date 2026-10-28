export interface CommitteeSummary {
    id: number;
    name: string;
    depth: number;
    responsible: string | null;
}

/** El logo, el lema y los objetivos de un comité (RF-083), con quién los cambió por última vez. */
export interface CommitteeIdentity {
    logoUrl: string | null;
    motto: string | null;
    objectives: string | null;
    updatedBy: string | null;
    updatedAt: string | null;
}

export interface CommitteeNode extends CommitteeSummary {
    children: CommitteeNode[];
}
