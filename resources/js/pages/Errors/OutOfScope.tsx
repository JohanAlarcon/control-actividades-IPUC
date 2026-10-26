import PageHeader from '@/components/page-header';
import MainLayout from '@/layouts/main-layout';
import { buttonClass } from '@/lib/styles';
import { Head, Link } from '@inertiajs/react';

/**
 * Lo ajeno, lo que no se permite y lo que no existe muestran esta misma pantalla, sin
 * un solo dato: así no se puede averiguar qué hay en otro comité (RF-008). No recibe
 * nada del servidor a propósito.
 */
export default function OutOfScope() {
    return (
        <MainLayout>
            <Head title="Información no disponible" />
            <PageHeader title="Información no disponible" />

            <p className="text-xl leading-relaxed">Esta información le corresponde a otro cargo, o ya no existe.</p>
            <p className="mt-3 text-lg leading-relaxed text-tinta-suave">Si la necesita, pídasela al pastor.</p>

            <Link href="/dashboard" className={`mt-8 ${buttonClass.primary}`}>
                Ir al inicio
            </Link>
        </MainLayout>
    );
}
