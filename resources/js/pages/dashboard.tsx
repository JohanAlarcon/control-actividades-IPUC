import MainLayout from '@/layouts/main-layout';
import type { SharedProps } from '@/types/shared';
import { Head, usePage } from '@inertiajs/react';

function greeting(): string {
    const hour = new Date().getHours();

    if (hour < 12) return 'Buenos días';
    if (hour < 19) return 'Buenas tardes';

    return 'Buenas noches';
}

/**
 * La entrada al sistema. Por ahora saluda y dice cuánto espera la decisión del cargo; la
 * pantalla de inicio completa, con lo que espera y desde cuándo, es la T084.
 */
export default function Dashboard() {
    const { auth, scope, pendingCount } = usePage<SharedProps>().props;
    const decides = auth.user?.isPastor || scope.activeRole?.role === 'president';

    return (
        <MainLayout>
            <Head title="Inicio" />
            <h1 className="text-2xl leading-tight font-bold">
                {greeting()}, {auth.user?.name}
            </h1>

            {decides && <PendingNotice count={pendingCount} />}

            <p className="mt-6 text-lg text-tinta-suave">
                Con el menú de abajo puede ver {auth.user?.isPastor ? 'los comités y administrar las cuentas' : 'cómo está organizado su comité'}.
            </p>
        </MainLayout>
    );
}

/** El número llega aparte, un instante después de la pantalla; mientras tanto no se muestra nada. */
function PendingNotice({ count }: { count?: number }) {
    if (count === undefined) return null;

    if (count === 0) {
        return <p className="mt-4 rounded-lg border border-linea/40 p-4 text-lg">No tiene solicitudes esperando su decisión.</p>;
    }

    return (
        <p className="mt-4 rounded-lg border-2 border-ipuc-dorado p-4 text-lg">
            <span className="font-bold">{count === 1 ? 'Una solicitud espera' : `${count} solicitudes esperan`}</span> su decisión.
        </p>
    );
}
