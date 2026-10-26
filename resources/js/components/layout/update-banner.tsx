import { watchForNewVersion } from '@/lib/update-check';
import { cardClass } from '@/lib/styles';
import { usePage } from '@inertiajs/react';
import { Sparkles } from 'lucide-react';
import { useEffect, useState } from 'react';

/** «Hay una versión nueva»: un aviso amable justo encima del menú, que se va al actualizar (T047). */
export default function UpdateBanner() {
    const { version } = usePage();
    const [isNew, setIsNew] = useState(false);

    useEffect(() => watchForNewVersion(version, () => setIsNew(true)), [version]);

    if (!isNew) return null;

    return (
        <div role="status" className="fixed inset-x-0 bottom-24 z-20 px-4">
            <div className={`mx-auto flex max-w-2xl items-center gap-3 p-4 ring-1 ring-ipuc-dorado/40 ${cardClass}`}>
                <span aria-hidden className="flex size-12 shrink-0 items-center justify-center rounded-xl bg-ipuc-dorado/25 text-tinta">
                    <Sparkles className="size-6" />
                </span>
                <p className="min-w-0 flex-1 text-lg leading-snug">
                    <span className="font-bold">Hay una versión nueva.</span> Actualice para usarla.
                </p>
                <button
                    type="button"
                    onClick={() => window.location.reload()}
                    className="h-12 shrink-0 rounded-xl bg-ipuc px-4 text-lg font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ipuc"
                >
                    Actualizar
                </button>
            </div>
        </div>
    );
}
