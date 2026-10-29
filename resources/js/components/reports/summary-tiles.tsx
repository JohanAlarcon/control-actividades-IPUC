import { summaryItems } from '@/lib/reports';
import { cardClass } from '@/lib/styles';
import type { ReportSummary } from '@/types/reports';

/** Las cifras de arriba (HU-11 escenario 1): de dos en dos en el celular. */
export default function SummaryTiles({ summary }: { summary: ReportSummary }) {
    return (
        <dl className="grid grid-cols-2 gap-3 lg:grid-cols-3">
            {summaryItems(summary).map((item) => (
                <div key={item.label} className={`p-4 ${cardClass}`}>
                    <dt className="text-lg text-tinta-suave">{item.label}</dt>
                    <dd className="text-2xl font-bold text-ipuc">{item.value}</dd>
                </div>
            ))}
        </dl>
    );
}
