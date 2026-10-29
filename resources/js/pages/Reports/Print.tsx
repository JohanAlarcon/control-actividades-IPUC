import ReportTable from '@/components/reports/report-table';
import { shortDate } from '@/lib/format';
import { membersUrl } from '@/lib/people';
import { summaryItems, withoutAgeText } from '@/lib/reports';
import { bigButtonClass, buttonClass } from '@/lib/styles';
import type { MemberFilters } from '@/types/people';
import type { Report } from '@/types/reports';
import type { SharedProps } from '@/types/shared';
import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft, Printer } from 'lucide-react';

interface Props {
    filters: MemberFilters;
    filterText: string;
    scopeNotice: string | null;
    report: Report;
}

/**
 * El reporte para imprimir (HU-12 escenario 2, T107): en blanco y negro, todo en tablas, con el nombre
 * de la sede, la fecha y los filtros en el encabezado. Arriba, solo en la pantalla, los botones.
 */
export default function ReportsPrint({ filters, filterText, scopeNotice, report }: Props) {
    const site = usePage<SharedProps>().props.site?.name ?? 'IPUC';
    const today = new Date();
    const withoutAge = withoutAgeText(report.summary.withoutAge);

    return (
        <div className="min-h-screen bg-fondo py-6 text-tinta print:bg-white print:py-0">
            <Head title="Reporte de miembros" />

            <div className="mx-auto mb-6 flex max-w-2xl flex-col gap-3 px-4 print:hidden">
                <h1 className="text-2xl font-bold text-ipuc">Reporte para imprimir</h1>
                <p className="text-lg text-tinta-suave">Sale en blanco y negro, con las cifras en tablas. Para enviarlo, escoja «Guardar como PDF» al imprimir.</p>
                <button type="button" onClick={() => window.print()} className={bigButtonClass}>
                    <Printer aria-hidden className="size-6" />
                    Imprimir
                </button>
                <Link href={membersUrl(filters, '/reports')} className={buttonClass.secondary}>
                    <ArrowLeft aria-hidden className="mr-2 size-6" />
                    Volver a los reportes
                </Link>
            </div>

            <article className="mx-auto max-w-2xl bg-white p-5 shadow-tarjeta sm:rounded-2xl sm:p-8 print:max-w-none print:p-0 print:text-black print:shadow-none">
                <header className="border-b-2 border-tinta pb-3 text-lg">
                    <p className="font-semibold tracking-wide uppercase">{site}</p>
                    <h2 className="text-2xl font-bold">Reporte de miembros</h2>
                    <p>
                        {shortDate(today.toISOString())} de {today.getFullYear()}
                    </p>
                    <p>Filtros: {filterText}</p>
                    {scopeNotice && <p>{scopeNotice}</p>}
                </header>

                <table className="mt-4 w-full text-left text-lg">
                    <tbody>
                        {summaryItems(report.summary).map((item) => (
                            <tr key={item.label} className="border-b border-linea/30">
                                <th scope="row" className="py-2 font-normal">
                                    {item.label}
                                </th>
                                <td className="py-2 text-right font-bold">{item.value}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
                {withoutAge && <p className="mt-2 text-lg">{withoutAge}</p>}

                {report.groups.map((group) => (
                    <section key={group.key} className="mt-6 break-inside-avoid">
                        {group.rows.length === 0 ? <p className="text-lg">{group.title}: nadie todavía.</p> : <ReportTable group={group} />}
                    </section>
                ))}
            </article>
        </div>
    );
}
