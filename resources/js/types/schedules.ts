/** Un cronograma interno en la lista (RF-086). Las fechas llegan sin hora: «2026-02-01». */
export interface InternalScheduleItem {
    id: number;
    name: string;
    committee: string;
    startsOn: string;
    endsOn: string;
    activities: number;
}

/** Una actividad de un cronograma interno, con quien la dirige (HU-27 escenario 2). */
export interface InternalActivity {
    id: number;
    title: string;
    theme: string | null;
    type: string | null;
    scheduledAt: string;
    endsAt: string;
    location: string | null;
    responsible: string | null;
    /** Cancelada sola, sin tocar las demás fechas de su serie (CL-30). No se borra. */
    cancelled: boolean;
}
