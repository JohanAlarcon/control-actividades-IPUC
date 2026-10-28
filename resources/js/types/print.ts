/** Una actividad en la hoja que imprime y comparte un comité (RF-089). */
export interface PrintActivity {
    id: number;
    title: string;
    scheduledAt: string;
    endsAt: string;
    location: string;
    committee: string;
    /** Quién la dirige, si se escribió; si no, está a cargo el comité. */
    responsible: string | null;
}

export interface PrintCommittee {
    id: number;
    name: string;
    motto: string | null;
    logoUrl: string | null;
}

/** Las fechas, sin hora: «2026-11-07». */
export interface PrintPeriod {
    from: string;
    to: string;
}
