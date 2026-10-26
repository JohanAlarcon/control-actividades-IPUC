import { STATUS_INFO } from '@/lib/schedule';
import type { ActivityStatus } from '@/types/schedule';

/** El estado de una actividad, con palabras y color: cumplida, en camino, aprobada o esperando (RF-026). */
export default function StatusBadge({ status }: { status: ActivityStatus }) {
    const { label, tone } = STATUS_INFO[status];

    return <span className={`inline-flex rounded-full px-3 py-0.5 text-lg font-semibold ${tone}`}>{label}</span>;
}
