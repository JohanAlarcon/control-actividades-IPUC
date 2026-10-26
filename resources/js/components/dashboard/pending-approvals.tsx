import InfoNote from '@/components/info-note';
import { waitingSince } from '@/lib/format';
import { cardClass } from '@/lib/styles';
import type { ApprovalItem } from '@/types/approvals';
import { Link } from '@inertiajs/react';
import { ChevronRight, CircleCheck, Clock } from 'lucide-react';

interface Props {
    items: ApprovalItem[];
    /** El total llega aparte, con el número del menú; mientras tanto se cuentan las que se ven. */
    count?: number;
    since?: string | null;
}

/** Lo primero del inicio: lo que espera la decisión del cargo y desde cuándo (HU-17, escenario 1; RF-054). */
export default function PendingApprovals({ items, count, since }: Props) {
    const total = count ?? items.length;

    if (total === 0) {
        return (
            <div className="mb-6">
                <InfoNote icon={CircleCheck}>No tiene solicitudes esperando su decisión.</InfoNote>
            </div>
        );
    }

    return (
        <section aria-labelledby="por-decidir" className="mb-6">
            <h2 id="por-decidir" className="px-1 text-xl font-bold">
                {total === 1 ? 'Una solicitud espera su decisión' : `${total} solicitudes esperan su decisión`}
            </h2>
            {since && <p className="mb-3 px-1 text-lg text-tinta-suave">La más antigua espera {waitingSince(since)}.</p>}

            <ul className={`mt-3 divide-y divide-ipuc/5 overflow-hidden ring-2 ring-ipuc-dorado/60 ${cardClass}`}>
                {items.map((item) => (
                    <li key={item.id}>
                        <Link
                            href="/approvals"
                            className="flex items-center gap-3 p-4 hover:bg-ipuc/5 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-ipuc active:bg-ipuc/10"
                        >
                            <span className="min-w-0 flex-1">
                                <span className="block text-lg font-bold break-words">{item.title}</span>
                                <span className="block text-lg text-tinta-suave">{item.committee}</span>
                                <span className="mt-1 flex items-center gap-2 text-lg font-semibold">
                                    <Clock aria-hidden className="size-5 shrink-0" />
                                    Espera {waitingSince(item.waitingSince)}
                                </span>
                            </span>
                            <ChevronRight aria-hidden className="size-6 shrink-0 text-tinta-suave" />
                        </Link>
                    </li>
                ))}
            </ul>
        </section>
    );
}
