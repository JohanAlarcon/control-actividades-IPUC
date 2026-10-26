import MainLayout from '@/layouts/main-layout';
import { buttonClass, cardClass } from '@/lib/styles';
import { Link } from '@inertiajs/react';
import { LockKeyhole } from 'lucide-react';

/**
 * Lo ajeno, lo que no se permite y lo que no existe muestran esta misma pantalla, sin
 * un solo dato: así no se puede averiguar qué hay en otro comité (RF-008). No recibe
 * nada del servidor a propósito.
 */
export default function OutOfScope() {
    return (
        <MainLayout title="Información no disponible">
            <div className={`flex flex-col items-center p-6 text-center ${cardClass}`}>
                <span aria-hidden className="flex size-16 items-center justify-center rounded-full bg-ipuc/10 text-ipuc">
                    <LockKeyhole className="size-8" />
                </span>
                <p className="mt-4 text-xl leading-relaxed font-semibold">Esta información le corresponde a otro cargo, o ya no existe.</p>
                <p className="mt-2 text-lg leading-relaxed text-tinta-suave">Si la necesita, pídasela al pastor.</p>

                <Link href="/dashboard" className={`mt-6 ${buttonClass.primary}`}>
                    Ir al inicio
                </Link>
            </div>
        </MainLayout>
    );
}
