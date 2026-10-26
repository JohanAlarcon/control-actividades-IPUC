import InfoNote from '@/components/info-note';
import SectionBlock from '@/components/section-block';
import MainLayout from '@/layouts/main-layout';
import { waitingSince } from '@/lib/format';
import { canPropose, decidesApprovals } from '@/lib/roles';
import { cardClass } from '@/lib/styles';
import type { SharedProps } from '@/types/shared';
import { Link, usePage } from '@inertiajs/react';
import { CalendarPlus, ChevronRight, CircleCheck, Inbox, Network, UsersRound, type LucideIcon } from 'lucide-react';

function greeting(): string {
    const hour = new Date().getHours();

    if (hour < 12) return 'Buenos días';
    if (hour < 19) return 'Buenas tardes';

    return 'Buenas noches';
}

/**
 * La entrada al sistema. Saluda, dice antes que nada cuánto espera la decisión del cargo y desde
 * cuándo (HU-06, escenario 4), y lleva a lo que ya existe. La pantalla de inicio completa es la T084.
 */
export default function Dashboard() {
    const { auth, scope, pendingCount, pendingSince } = usePage<SharedProps>().props;
    const decides = decidesApprovals(auth.user?.isPastor, scope.activeRole?.role);

    return (
        <MainLayout title={greeting()} subtitle={auth.user?.name}>
            {decides && pendingCount !== undefined && (
                <div className="mb-6">
                    {pendingCount === 0 ? (
                        <InfoNote icon={CircleCheck}>No tiene solicitudes esperando su decisión.</InfoNote>
                    ) : (
                        <Link href="/approvals" className="block rounded-2xl focus-visible:outline-2 focus-visible:outline-ipuc">
                            <InfoNote icon={Inbox} tone="gold">
                                <span className="font-bold">{pendingCount === 1 ? 'Una solicitud espera' : `${pendingCount} solicitudes esperan`} su decisión.</span>
                                {pendingSince && ` ${pendingCount === 1 ? 'Espera' : 'La más antigua espera'} ${waitingSince(pendingSince)}.`}
                                <span className="mt-1 flex items-center gap-1 font-bold text-ipuc">
                                    Revisarlas <ChevronRight aria-hidden className="size-5" />
                                </span>
                            </InfoNote>
                        </Link>
                    )}
                </div>
            )}

            <SectionBlock title="¿Qué quiere hacer?">
                <ul className={`divide-y divide-ipuc/5 overflow-hidden ${cardClass} lg:grid lg:grid-cols-2 lg:gap-4 lg:divide-y-0 lg:overflow-visible lg:bg-transparent lg:shadow-none`}>
                    <Shortcut
                        href="/approvals"
                        icon={Inbox}
                        title={decides ? 'Aprobar solicitudes' : 'Ver las solicitudes'}
                        text={decides ? 'Lo que proponen los comités y espera su decisión.' : 'En qué van las solicitudes de su comité.'}
                    />
                    {canPropose(scope.activeRole?.role) && (
                        <Shortcut href="/activities/create" icon={CalendarPlus} title="Proponer una actividad" text="Algo que no estaba en el plan del año." />
                    )}
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
        <li className="lg:overflow-hidden lg:rounded-2xl lg:bg-white lg:shadow-tarjeta">
            <Link href={href} className="flex items-center gap-4 p-4 hover:bg-ipuc/5 lg:h-full focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-ipuc active:bg-ipuc/10">
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
