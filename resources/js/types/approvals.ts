export type ApprovalType = 'activity' | 'expense' | 'task' | 'date_change' | 'new_member';

/** Una solicitud, tal como la manda la bandeja (contracts/pages.md, Aprobaciones). */
export interface ApprovalItem {
    id: number;
    type: ApprovalType;
    title: string;
    detail: string | null;
    amount: number | null;
    committee: string;
    requestedBy: string;
    escalatedBy: string | null;
    level: 'president' | 'pastor';
    status: 'pending' | 'approved' | 'rejected';
    waitingSince: string;
    resolvedBy: string | null;
    resolvedAt: string | null;
    reason: string | null;
    activity: { date: string; location: string | null } | null;
}
