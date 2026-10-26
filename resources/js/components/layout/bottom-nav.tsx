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
        <nav aria-label="Menú principal" className="fixed inset-x-0 bottom-0 border-t border-linea/40 bg-white pb-[env(safe-area-inset-bottom)]">
            <ul className="mx-auto flex max-w-2xl">
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
                className={`flex min-h-16 flex-col items-center justify-center gap-1 text-lg ${active ? 'font-bold text-ipuc' : 'text-tinta-suave'}`}
            >
                <span className="relative">
                    <Icon aria-hidden className="size-6" />
                    {item.badge ? (
                        <span aria-hidden className="absolute -top-3 -right-5 flex h-7 min-w-7 items-center justify-center rounded-full bg-ipuc-dorado px-1 text-lg font-bold text-tinta">
                            {item.badge}
                        </span>
                    ) : null}
                </span>
                {item.label}
                {item.badge ? <span className="sr-only">: {item.badge} esperando su decisión</span> : null}
            </Link>
        </li>
    );
}
