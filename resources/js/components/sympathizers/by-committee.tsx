import { cardClass } from '@/lib/styles';

export interface CommitteeCount {
    committee: string;
    total: number;
    drifting: number;
}

/** Para BIS y el pastor: cuántos acompaña cada comité, y cuántos se le están alejando (HU-14 escenario 1). */
export default function ByCommittee({ rows }: { rows: CommitteeCount[] }) {
    if (rows.length === 0) return null;

    return (
        <section aria-labelledby="por-comite" className="mb-6">
            <h2 id="por-comite" className="mb-3 px-1 text-xl font-bold">
                Por comité
            </h2>
            <ul className={`divide-y divide-ipuc/5 ${cardClass}`}>
                {rows.map((row) => (
                    <li key={row.committee} className="flex flex-wrap items-baseline justify-between gap-x-3 px-4 py-3 text-lg">
                        <span className="font-semibold break-words">{row.committee}</span>
                        <span className="text-tinta-suave">
                            {row.total === 1 ? 'una persona' : `${row.total} personas`}
                            {row.drifting > 0 && <span className="font-semibold text-alerta"> · {row.drifting} alejándose</span>}
                        </span>
                    </li>
                ))}
            </ul>
        </section>
    );
}
