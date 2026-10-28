import { buttonClass } from '@/lib/styles';
import { Link } from '@inertiajs/react';
import { TriangleAlert } from 'lucide-react';

/**
 * Puede que ya esté registrada en otro comité (CL-09, HU-13 escenario 4): el aviso no dice dónde ni
 * muestra su ficha, y deja decidir sin perder lo escrito.
 */
export default function DuplicateWarning({ message, busy, onInsist }: { message: string; busy: boolean; onInsist: () => void }) {
    return (
        <div role="alert" className="rounded-2xl bg-ipuc-dorado/20 p-4 ring-1 ring-ipuc-dorado/60">
            <p className="flex items-center gap-2 text-lg font-bold">
                <TriangleAlert aria-hidden className="size-6 shrink-0" />
                Puede que ya esté registrada
            </p>
            <p className="mt-2 text-lg leading-relaxed">{message}</p>
            <div className="mt-4 grid gap-3 sm:grid-cols-2">
                <Link href="/sympathizers" className={buttonClass.secondary}>
                    No registrarla
                </Link>
                <button type="button" onClick={onInsist} disabled={busy} className={buttonClass.primary}>
                    Es otra persona: registrarla
                </button>
            </div>
        </div>
    );
}
