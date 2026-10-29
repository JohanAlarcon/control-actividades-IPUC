import { barWidths } from '@/lib/reports';
import type { ReportGroup } from '@/types/reports';

/**
 * Un grupo de cifras en barras, sin librería de gráficos (T102). Cada barra lleva su nombre y su
 * número escritos, así que se lee a 360 px sin girar el teléfono; la barra es solo la ayuda visual.
 */
export default function ReportBars({ group }: { group: ReportGroup }) {
    const widths = barWidths(group.rows);

    return (
        <ul className="mt-3 flex flex-col gap-3">
            {group.rows.map((row, index) => (
                <li key={row.label}>
                    <div className="flex items-baseline justify-between gap-3 text-lg">
                        <span className="min-w-0 break-words">{row.label}</span>
                        <span className="shrink-0 font-bold">{row.count}</span>
                    </div>
                    <div aria-hidden className="mt-1 h-3 rounded-full bg-ipuc/10">
                        <div className="h-3 rounded-full bg-ipuc" style={{ width: `${widths[index]}%` }} />
                    </div>
                </li>
            ))}
        </ul>
    );
}
