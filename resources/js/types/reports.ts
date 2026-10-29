export interface ReportRow {
    label: string;
    count: number;
}

/** Un grupo de cifras de RF-036, con sus nombres ya puestos por el servidor. */
export interface ReportGroup {
    key: string;
    title: string;
    rows: ReportRow[];
}

export interface ReportSummary {
    total: number;
    baptized: number;
    baptizedPercent: number;
    married: number;
    single: number;
    averageAge: number | null;
    withoutCommittee: number;
    withoutAge: number;
    missingConsent: number;
    pendingProfiles: number;
}

export interface Report {
    summary: ReportSummary;
    groups: ReportGroup[];
}
