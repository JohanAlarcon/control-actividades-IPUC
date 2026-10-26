import InfoNote from '@/components/info-note';
import SectionBlock from '@/components/section-block';
import MainLayout from '@/layouts/main-layout';
import { cardClass } from '@/lib/styles';
import type { SharedProps } from '@/types/shared';
import { Link, usePage } from '@inertiajs/react';
import { ChevronRight, CircleCheck, Inbox, Network, UsersRound, type LucideIcon } from 'lucide-react';

function greeting(): string {
    const hour = new Date().getHours();

    if (hour < 12) return 'Buenos días';
    if (hour < 19) return 'Buenas tardes';

    return 'Buenas noches';
}

/**
 * La entrada al sistema. Saluda, dice cuánto espera la decisión del cargo y lleva a lo que ya
 * existe. La pantalla de inicio completa, con lo que espera y desde cuándo, es la T084.
 */
export default function Dashboard() {
    const { auth, scope, pendingCount } = usePage<SharedProps>().props;
    const decides = auth.user?.isPastor || scope.activeRole?.role === 'president';

    return (
        <MainLayout title={greeting()} subtitle={auth.user?.name}>
            {decides && pendingCount !== undefined && (
                <div className="mb-6">
                    {pendingCount === 0 ? (
                        <InfoNote icon={CircleCheck}>No tiene solicitudes esperando su decisión.</InfoNote>
                    ) : (
                        <InfoNote icon={Inbox} tone="gold">
                            <span className="font-bold">{pendingCount === 1 ? 'Una solicitud espera' : `${pendingCount} solicitudes esperan`}</span> su
                            decisión.
                        </InfoNote>
                    )}
                </div>
            )}

            <SectionBlock title="¿Qué quiere hacer?">
                <ul className={`divide-y divide-ipuc/5 overflow-hidden ${cardClass}`}>
                    <Shortcut href="/committees" icon={Network} title="Ver los comités" text="Cómo está organizada la congregación y quién encabeza cada parte." />
                    {auth.user?.isPastor && (
                        <Shortcut href="/users" icon={UsersRound} title="Administrar las cuentas" text="Crear cuentas, asignar cargos y restablecer claves." />
                    )}
                </ul>
            </SectionBlock>
        </MainLayout>
    );
}

function Shortcut({ href, icon: Icon, title, text }: { href: string; icon: LucideIcon; title: string; text: string }) {
    return (
        <li>
            <Link href={href} className="flex items-center gap-4 p-4 hover:bg-ipuc/5 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-ipuc active:bg-ipuc/10">
                <span aria-hidden className="flex size-12 shrink-0 items-center justify-center rounded-xl bg-ipuc text-white">
                    <Icon className="size-6" />
                </span>
                <span className="min-w-0 flex-1">
                    <span className="block text-lg font-bold">{title}</span>
                    <span className="block text-lg text-tinta-suave">{text}</span>
                </span>
                <ChevronRight aria-hidden className="size-6 shrink-0 text-tinta-suave" />
            </Link>
        </li>
    );
}
