import PrintSheet from '@/components/schedule/print-sheet';
import { useFitWidth } from '@/hooks/use-fit-width';
import { MONTHS, scheduleUrl } from '@/lib/schedule';
import { bigButtonClass, buttonClass } from '@/lib/styles';
import type { MonthGroup, MonthNoteItem, ScheduleFilters } from '@/types/schedule';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Printer } from 'lucide-react';

interface Props {
    filters: ScheduleFilters;
    months: MonthGroup[];
    notes?: MonthNoteItem[];
}

/** Una carta horizontal, en píxeles de pantalla: 11 pulgadas de ancho. */
const SHEET_WIDTH = 1056;

/**
 * El cronograma para imprimir o guardar como PDF (RF-117, HU-09 escenario 7): una hoja por mes con
 * el formato de la sede, sin el molde de las pantallas. Arriba, solo en la pantalla, los botones.
 */
export default function SchedulePrint({ filters, months, notes = [] }: Props) {
    const zoom = useFitWidth(SHEET_WIDTH);
    const title = filters.month ? `Cronograma de ${MONTHS[filters.month - 1].toLowerCase()} ${filters.year}` : `Cronograma ${filters.year}`;

    return (
        <div className="min-h-screen bg-fondo py-6 text-tinta print:bg-white print:py-0">
            <Head title={title} />

            <div className="mx-auto mb-6 flex max-w-2xl flex-col gap-3 px-4 print:hidden">
                <h1 className="text-2xl font-bold text-ipuc">{title}</h1>
                <p className="text-lg text-tinta-suave">
                    Sale una hoja horizontal por mes. Para enviarlo por WhatsApp, escoja «Guardar como PDF» al imprimir y comparta el archivo.
                </p>
                <button type="button" onClick={() => window.print()} className={bigButtonClass}>
                    <Printer aria-hidden className="size-6" />
                    Imprimir
                </button>
                <Link href={scheduleUrl(filters)} className={buttonClass.secondary}>
                    <ArrowLeft aria-hidden className="mr-2 size-6" />
                    Volver al cronograma
                </Link>
            </div>

            {months.map((group) => (
                <PrintSheet
                    key={group.month}
                    year={filters.year}
                    month={group.month}
                    activities={group.activities}
                    notes={notes.filter((note) => note.month === group.month)}
                    zoom={zoom}
                />
            ))}
        </div>
    );
}
