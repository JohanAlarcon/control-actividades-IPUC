export interface CommitteeSummary {
    id: number;
    name: string;
    depth: number;
    responsible: string | null;
}

export interface CommitteeNode extends CommitteeSummary {
    children: CommitteeNode[];
}
