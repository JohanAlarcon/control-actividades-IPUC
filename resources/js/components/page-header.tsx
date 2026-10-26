import { Link } from '@inertiajs/react';
import { ChevronLeft } from 'lucide-react';

interface Props {
    title: string;
    backHref?: string;
    backLabel?: string;
}

/** El título va dentro del contenido, como en el prototipo: la franja azul de arriba es de la aplicación. */
export default function PageHeader({ title, backHref, backLabel = 'Volver' }: Props) {
    return (
        <div className="mb-6">
            {backHref && (
                <Link href={backHref} className="-ml-2 inline-flex h-12 items-center gap-1 rounded-lg px-2 text-lg font-semibold text-ipuc">
                    <ChevronLeft aria-hidden className="size-5" />
                    {backLabel}
                </Link>
            )}
            <h1 className="text-2xl leading-tight font-bold">{title}</h1>
        </div>
    );
}
