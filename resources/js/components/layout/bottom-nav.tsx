import type { SharedProps } from '@/types/shared';
import { Link, usePage } from '@inertiajs/react';
import { House, Network, UsersRound, type LucideIcon } from 'lucide-react';

interface Item {
    href: string;
    label: string;
    icon: LucideIcon;
    badge?: number;
}

/**
 * El menú del prototipo: abajo, siempre a la vista, con la palabra debajo de cada icono.
 * Solo trae las secciones que ya existen; Cronograma y Dinero se suman cuando estén.
 * El número sobre «Inicio» es lo que espera la decisión del cargo (RF-055).
 */
export default function BottomNav() {
    const { props, url } = usePage<SharedProps>();

    const items: Item[] = [
        { href: '/dashboard', label: 'Inicio', icon: House, badge: props.pendingCount },
        { href: '/committees', label: 'Comités', icon: Network },
        ...(props.auth.user?.isPastor ? [{ href: '/users', label: 'Cuentas', icon: UsersRound }] : []),
    ];

    return (
        <nav aria-label="Menú principal" className="fixed inset-x-0 bottom-0 z-10 bg-white/95 pb-[env(safe-area-inset-bottom)] shadow-barra backdrop-blur">
            <ul className="mx-auto flex max-w-2xl px-2">
                {items.map((item) => (
                    <NavItem key={item.href} item={item} active={url.startsWith(item.href)} />
                ))}
            </ul>
        </nav>
    );
}

function NavItem({ item, active }: { item: Item; active: boolean }) {
    const Icon = item.icon;

    return (
        <li className="flex-1">
            <Link
                href={item.href}
                aria-current={active ? 'page' : undefined}
                className="flex min-h-16 flex-col items-center justify-center gap-0.5 py-2 text-lg focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-ipuc"
            >
                <span className={`relative flex h-8 w-16 items-center justify-center rounded-full ${active ? 'bg-ipuc/10 text-ipuc' : 'text-tinta-suave'}`}>
                    <Icon aria-hidden className="size-6" />
                    {item.badge ? (
                        <span aria-hidden className="absolute -top-2 right-0 flex h-6 min-w-6 items-center justify-center rounded-full bg-ipuc-dorado px-1 text-base font-bold text-tinta ring-2 ring-white">
                            {item.badge}
                        </span>
                    ) : null}
                </span>
                <span className={active ? 'font-bold text-ipuc' : 'text-tinta-suave'}>{item.label}</span>
                {item.badge ? <span className="sr-only">: {item.badge} esperando su decisión</span> : null}
            </Link>
        </li>
    );
}
