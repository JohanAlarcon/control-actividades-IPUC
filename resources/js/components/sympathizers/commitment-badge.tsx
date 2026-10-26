import { COMMITMENT_INFO } from '@/lib/people';
import type { CommitmentLevel } from '@/types/people';

/** El nivel de compromiso, calculado por el sistema: creciendo, estable o alejándose (RF-043). */
export default function CommitmentBadge({ level }: { level: CommitmentLevel }) {
    const { label, tone } = COMMITMENT_INFO[level];

    return <span className={`inline-flex rounded-full px-3 py-0.5 text-lg font-semibold ${tone}`}>{label}</span>;
}
