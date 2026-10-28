export type ActivityStatus = 'pending' | 'approved' | 'in_progress' | 'completed' | 'rejected';

/** La marca de color del cronograma de la sede (RF-116): rojo la primera, naranja las otras dos. */
export type Highlight = 'special_day' | 'district' | 'national';

/** Los seis datos que se comparten de cualquier actividad, y su número para abrirla (RF-010). */
export interface SharedActivity {
    id: number;
    title: string;
    scheduledAt: string;
    endsAt: string;
    location: string | null;
    committee: string;
    status: ActivityStatus;
}

/** Una actividad del propio comité: lo compartido, más lo que solo ve su comité. */
export interface OwnActivity extends SharedActivity {
    description: string | null;
    theme: string | null;
    highlight: Highlight | null;
    committeeId: number;
    type: string | null;
    progress: number;
    budget: number | null;
    fromPlan: boolean;
}

export type CalendarActivity = SharedActivity | OwnActivity;

export interface ScheduleSummary {
    total: number;
    completed: number;
    inProgress: number;
    approved: number;
    pending: number;
    due: number;
    compliance: number | null;
}

/** La nota general de un mes (RF-118), junto a su nombre en el cronograma. */
export interface MonthNoteItem {
    id: number;
    month: number;
    text: string;
    highlight: Highlight | null;
}

export interface MonthGroup {
    month: number;
    activities: CalendarActivity[];
}

export interface ScheduleFilters {
    year: number;
    committee: number | null;
    month: number | null;
    status: ActivityStatus | null;
}

export interface AnnualPlanInfo {
    year: number;
    approved: boolean;
    approvedBy: string | null;
    approvedAt: string | null;
    waiting: number;
    canApprove: boolean;
    canLoad: boolean;
}

export interface CommitteeOption {
    id: number;
    name: string;
    depth: number;
}

export interface TaskItem {
    id: number;
    description: string;
    assignedTo: string | null;
    done: boolean;
    doneAt: string | null;
}

export interface TrailEvent {
    date: string;
    text: string;
    reason?: string | null;
}
