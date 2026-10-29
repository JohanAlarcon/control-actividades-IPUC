import InfoNote from '@/components/info-note';
import MemberFilters from '@/components/members/member-filters';
import ReportGroupCard, { type ReportView } from '@/components/reports/report-group-card';
import SummaryTiles from '@/components/reports/summary-tiles';
import MainLayout from '@/layouts/main-layout';
import { membersUrl } from '@/lib/people';
import { withoutAgeText } from '@/lib/reports';
import { buttonClass } from '@/lib/styles';
import type { MemberFilters as Filters } from '@/types/people';
import type { Report } from '@/types/reports';
import type { CommitteeOption } from '@/types/schedule';
import { Link } from '@inertiajs/react';
import { ChartColumn, Download, Info, Printer, Table } from 'lucide-react';
import { useState } from 'react';

interface Props {
    filters: Filters;
    scopeNotice: string | null;
    /** Las cifras llegan aparte, un momento después de la pantalla. */
    report?: Report;
    committees?: CommitteeOption[];
}

/**
 * Los reportes de los miembros (HU-11, HU-12): las cifras de arriba y cada grupo en barras o en
 * tabla, con los mismos filtros de la lista. Se imprimen y se descargan tal como se ven.
 */
export default function ReportsIndex({ filters, scopeNotice, report, committees }: Props) {
    const [view, setView] = useState<ReportView>('charts');
    const withoutAge = report ? withoutAgeText(report.summary.withoutAge) : null;

    return (
        <MainLayout title="Reportes" subtitle={scopeNotice ? 'De la gente de su cargo' : 'De toda la congregación'}>
            {scopeNotice && <InfoNote icon={Info}>{scopeNotice}</InfoNote>}
            <MemberFilters filters={filters} committees={committees} path="/reports" />

            <div role="group" aria-label="Cómo ver las cifras" className="mb-6 grid grid-cols-2 gap-3 lg:w-96">
                <ViewButton active={view === 'charts'} onClick={() => setView('charts')} icon={ChartColumn} label="En barras" />
                <ViewButton active={view === 'tables'} onClick={() => setView('tables')} icon={Table} label="En tablas" />
            </div>

            {report ? (
                <div className="flex flex-col gap-6">
                    <SummaryTiles summary={report.summary} />
                    {withoutAge && <InfoNote icon={Info}>{withoutAge}</InfoNote>}
                    <div className="grid gap-6 lg:grid-cols-2 lg:items-start">
                        {report.groups.map((group) => (
                            <ReportGroupCard key={group.key} group={group} view={view} />
                        ))}
                    </div>
                </div>
            ) : (
                <p className="text-lg text-tinta-suave">Contando las cifras…</p>
            )}

            <div className="mt-6 grid gap-3 sm:grid-cols-2 lg:w-[40rem]">
                <a href={membersUrl(filters, '/reports/download')} className={buttonClass.primary}>
                    <Download aria-hidden className="mr-2 size-6" />
                    Descargar en Excel
                </a>
                <Link href={membersUrl(filters, '/reports/print')} className={buttonClass.secondary}>
                    <Printer aria-hidden className="mr-2 size-6" />
                    Imprimir
                </Link>
            </div>
        </MainLayout>
    );
}

function ViewButton({ active, onClick, icon: Icon, label }: { active: boolean; onClick: () => void; icon: typeof Table; label: string }) {
    return (
        <button type="button" aria-pressed={active} onClick={onClick} className={active ? buttonClass.primary : buttonClass.secondary}>
            <Icon aria-hidden className="mr-2 size-6" />
            {label}
        </button>
    );
}
