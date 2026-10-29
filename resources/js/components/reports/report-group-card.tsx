import ReportBars from '@/components/reports/report-bars';
import ReportTable from '@/components/reports/report-table';
import { cardClass } from '@/lib/styles';
import type { ReportGroup } from '@/types/reports';

export type ReportView = 'charts' | 'tables';

/** Un grupo de cifras en su tarjeta: en barras o en tabla, según escoja la persona. Un grupo vacío lo dice. */
export default function ReportGroupCard({ group, view }: { group: ReportGroup; view: ReportView }) {
    return (
        <section aria-labelledby={`grupo-${group.key}`} className={`p-4 ${cardClass}`}>
            <h2 id={`grupo-${group.key}`} className="text-xl font-bold">
                {group.title}
            </h2>
            {group.rows.length === 0 ? (
                <p className="mt-2 text-lg text-tinta-suave">Nadie todavía.</p>
            ) : view === 'charts' ? (
                <ReportBars group={group} />
            ) : (
                <ReportTable group={group} titled={false} />
            )}
        </section>
    );
}
