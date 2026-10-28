import SectionBlock from '@/components/section-block';
import { canPropose, decidesApprovals } from '@/lib/roles';
import { cardClass } from '@/lib/styles';
import type { SharedProps } from '@/types/shared';
import { Link, usePage } from '@inertiajs/react';
import { CalendarPlus, CalendarRange, ChevronRight, HeartHandshake, Inbox, Network, Quote, Shapes, UserPlus, UserRound, UsersRound, type LucideIcon } from 'lucide-react';

/** «¿Qué quiere hacer?»: los atajos del cargo, incluidos los que no caben en el menú del celular. */
export default function ShortcutList() {
    const { auth, scope } = usePage<SharedProps>().props;
    const isPastor = Boolean(auth.user?.isPastor);
    const decides = decidesApprovals(auth.user?.isPastor, scope.activeRole?.role);

    return (
        <SectionBlock title="¿Qué quiere hacer?">
            <ul className={`divide-y divide-ipuc/5 overflow-hidden ${cardClass} lg:grid lg:grid-cols-2 lg:gap-4 lg:divide-y-0 lg:overflow-visible lg:bg-transparent lg:shadow-none`}>
                <Shortcut
                    href="/approvals"
                    icon={Inbox}
                    title={isPastor ? 'Aprobar solicitudes' : decides ? 'Revisar solicitudes' : 'Ver las solicitudes'}
                    text={
                        isPastor
                            ? 'Lo que llega de los comités y espera su decisión.'
                            : decides
                              ? 'Usted les da el visto bueno; el pastor es quien aprueba.'
                              : 'En qué van las solicitudes de su comité.'
                    }
                />
                {canPropose(scope.activeRole?.role) && (
                    <Shortcut href="/activities/create" icon={CalendarPlus} title="Proponer una actividad" text="Algo que no estaba en el plan del año." />
                )}
                <Shortcut href="/schedules" icon={CalendarRange} title="Cronogramas internos" text="Los ensayos, las clases y las reuniones de su comité, aparte del cronograma general." />
                <Shortcut href="/committees" icon={Network} title="Ver los comités" text="Cómo está organizada la congregación y quién encabeza cada parte." />
                <Shortcut href="/members" icon={UserRound} title="Miembros" text="Los hermanos de su cargo: buscarlos, registrarlos y corregir su ficha." />
                <Shortcut href="/sympathizers/create" icon={UserPlus} title="Registrar a un simpatizante" text="Alguien que visitó la iglesia, para que no se pierda de vista." />
                {auth.followsSympathizers && (
                    <Shortcut href="/sympathizers" icon={HeartHandshake} title="Acompañar a los simpatizantes" text="Cómo va cada persona que visita la iglesia, y quién se está alejando." />
                )}
                {isPastor && <Shortcut href="/users" icon={UsersRound} title="Administrar las cuentas" text="Crear cuentas, asignar cargos y restablecer claves." />}
                {isPastor && <Shortcut href="/site-motto" icon={Quote} title="Lema del año" text="El que se ve en la entrada y en todas las pantallas." />}
                {isPastor && (
                    <Shortcut href="/activity-types" icon={Shapes} title="Clases de actividad" text="Las que se ofrecen al proponer: agregar, cambiar el nombre o desactivar." />
                )}
            </ul>
        </SectionBlock>
    );
}

function Shortcut({ href, icon: Icon, title, text }: { href: string; icon: LucideIcon; title: string; text: string }) {
    return (
        <li className="lg:overflow-hidden lg:rounded-2xl lg:bg-white lg:shadow-tarjeta">
            <Link
                href={href}
                className="flex items-center gap-4 p-4 hover:bg-ipuc/5 lg:h-full focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-ipuc active:bg-ipuc/10"
            >
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
