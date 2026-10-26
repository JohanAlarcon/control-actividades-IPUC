import type { SharedProps } from '@/types/shared';
import { usePage } from '@inertiajs/react';
import { CircleCheck, X } from 'lucide-react';
import { useEffect, useState } from 'react';

/** Cuánto se queda a la vista: lo suficiente para leerlo con calma. También se puede cerrar. */
const VISIBLE_MS = 8000;

/**
 * La confirmación de que algo quedó guardado. Flota arriba de todo para que se vea aunque la
 * pantalla esté más abajo, por ejemplo al aprobar la última tarjeta de la lista.
 */
export default function NoticeToast() {
    const { flash } = usePage<SharedProps>().props;
    const [notice, setNotice] = useState<string | null>(null);

    useEffect(() => {
        if (!flash.notice) return;

        setNotice(flash.notice);
        const timer = setTimeout(() => setNotice(null), VISIBLE_MS);

        return () => clearTimeout(timer);
    }, [flash]);

    return (
        <div role="status" aria-live="polite" className="pointer-events-none fixed inset-x-0 top-3 z-30 px-4 lg:left-72">
            {notice && (
                <div className="pointer-events-auto mx-auto flex max-w-xl items-center gap-3 rounded-2xl bg-white py-2 pr-2 pl-4 shadow-xl ring-2 ring-ipuc-claro">
                    <CircleCheck aria-hidden className="size-7 shrink-0 text-ipuc" />
                    <p className="flex-1 py-1 text-lg font-semibold">{notice}</p>
                    <button
                        type="button"
                        onClick={() => setNotice(null)}
                        aria-label="Cerrar el aviso"
                        className="flex size-12 shrink-0 items-center justify-center rounded-full text-tinta-suave hover:bg-ipuc/5 focus-visible:outline-2 focus-visible:outline-ipuc"
                    >
                        <X aria-hidden className="size-6" />
                    </button>
                </div>
            )}
        </div>
    );
}
