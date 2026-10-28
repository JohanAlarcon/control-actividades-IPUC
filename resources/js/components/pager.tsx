import { buttonClass } from '@/lib/styles';
import { ChevronLeft, ChevronRight } from 'lucide-react';

interface Props {
    page: number;
    pages: number;
    onChange: (page: number) => void;
    /** El `id` del comienzo de la lista: al cambiar de página se vuelve ahí, no al principio de la pantalla. */
    anchor: string;
}

/** El paginador de las listas largas (RF-122): en qué página va y dos botones grandes. Con una sola página no aparece. */
export default function Pager({ page, pages, onChange, anchor }: Props) {
    if (pages <= 1) return null;

    function go(to: number) {
        onChange(to);
        document.getElementById(anchor)?.scrollIntoView({ block: 'start' });
    }

    return (
        <nav aria-label="Páginas de la lista" className="mt-5 flex flex-col gap-3">
            <p aria-live="polite" className="text-center text-lg font-semibold">
                Página {page} de {pages}
            </p>
            <div className="grid grid-cols-2 gap-3 lg:mx-auto lg:w-96">
                <button type="button" disabled={page === 1} onClick={() => go(page - 1)} className={buttonClass.secondary}>
                    <ChevronLeft aria-hidden className="mr-1 size-6" />
                    Anterior
                </button>
                <button type="button" disabled={page === pages} onClick={() => go(page + 1)} className={buttonClass.secondary}>
                    Siguiente
                    <ChevronRight aria-hidden className="ml-1 size-6" />
                </button>
            </div>
        </nav>
    );
}
