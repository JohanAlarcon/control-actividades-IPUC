import ActivitiesSheet from '@/components/print/activities-sheet';
import PrintFilters from '@/components/print/print-filters';
import { shareText, whatsappUrl } from '@/lib/share';
import { bigButtonClass, buttonClass } from '@/lib/styles';
import type { PrintActivity, PrintCommittee, PrintPeriod } from '@/types/print';
import type { CommitteeOption } from '@/types/schedule';
import type { SharedProps } from '@/types/shared';
import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft, MessageCircle, Printer } from 'lucide-react';

interface Props {
    committee: PrintCommittee;
    period: PrintPeriod;
    activities: PrintActivity[];
    committees?: CommitteeOption[];
}

/**
 * Imprimir y compartir lo de un comité (HU-30): arriba, solo en la pantalla, el comité, las fechas
 * y los botones; debajo, la hoja. WhatsApp se abre con el resumen escrito y la persona lo envía.
 */
export default function PrintActivities({ committee, period, activities, committees }: Props) {
    const site = usePage<SharedProps>().props.site?.name ?? 'IPUC';

    return (
        <div className="min-h-screen bg-fondo py-6 text-tinta print:bg-white print:py-0">
            <Head title={`Actividades de ${committee.name}`} />

            <div className="mx-auto mb-6 flex max-w-2xl flex-col gap-3 px-4 print:hidden">
                <h1 className="text-2xl font-bold text-ipuc">Imprimir y compartir</h1>
                <p className="text-lg text-tinta-suave">
                    Salen las actividades aprobadas del comité y de sus sub-comités, del cronograma general y de los internos.
                </p>
                <PrintFilters key={`${committee.id}:${period.from}:${period.to}`} committee={committee} period={period} committees={committees} />
                {activities.length > 0 && (
                    <a href={whatsappUrl(shareText(site, committee.name, period, activities))} target="_blank" rel="noopener noreferrer" className={bigButtonClass}>
                        <MessageCircle aria-hidden className="size-6" />
                        Compartir por WhatsApp
                    </a>
                )}
                <button type="button" onClick={() => window.print()} className={buttonClass.secondary}>
                    <Printer aria-hidden className="mr-2 size-6" />
                    Imprimir
                </button>
                <Link href={`/committees/${committee.id}`} className={buttonClass.secondary}>
                    <ArrowLeft aria-hidden className="mr-2 size-6" />
                    Volver al comité
                </Link>
            </div>

            <ActivitiesSheet site={site} committee={committee} period={period} activities={activities} />
        </div>
    );
}
