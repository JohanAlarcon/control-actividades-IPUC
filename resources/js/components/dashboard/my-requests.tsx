import { requestState } from '@/lib/approvals';
import { cardClass } from '@/lib/styles';
import type { ApprovalItem } from '@/types/approvals';
import { Link } from '@inertiajs/react';

/** En qué va lo que la persona envió: quién la tiene, o cómo se resolvió y por qué (HU-17, escenario 3). */
export default function MyRequests({ items }: { items: ApprovalItem[] }) {
    return (
        <section aria-labelledby="mis-solicitudes" className="mb-6">
            <h2 id="mis-solicitudes" className="mb-3 px-1 text-xl font-bold">
                Lo que usted envió
            </h2>
            <ul className={`divide-y divide-ipuc/5 overflow-hidden ${cardClass}`}>
                {items.map((item) => (
                    <li key={item.id} className="p-4">
                        <p className="text-lg font-bold break-words">{item.title}</p>
                        <p
                            className={`text-lg ${item.status === 'rejected' ? 'font-semibold text-alerta' : item.status === 'approved' ? 'font-semibold text-ipuc' : 'text-tinta-suave'}`}
                        >
                            {requestState(item)}
                        </p>
                        {item.reason && <p className="mt-1 text-lg">Motivo: {item.reason}</p>}
                    </li>
                ))}
            </ul>
            <Link href="/approvals" className="mt-3 inline-block px-1 text-lg font-semibold text-ipuc underline underline-offset-4">
                Ver todas las solicitudes
            </Link>
        </section>
    );
}
