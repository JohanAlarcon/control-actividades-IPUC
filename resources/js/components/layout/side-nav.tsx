import { navItems, type MenuItem } from '@/lib/navigation';
import type { SharedProps } from '@/types/shared';
import { Link, usePage } from '@inertiajs/react';

/**
 * El menú en el escritorio, de 1024 px en adelante: al costado, con el logo arriba. Trae las
 * mismas secciones que el menú de abajo del celular, que en esa medida se esconde.
 */
export default function SideNav() {
    const { props, url } = usePage<SharedProps>();
    const site = props.site;

    return (
        <nav aria-label="Menú principal" className="fixed inset-y-0 left-0 z-10 hidden w-72 flex-col gap-8 bg-ipuc-oscuro px-4 py-5 text-white lg:flex">
            <Link href="/dashboard" className="flex items-center gap-3 rounded-xl px-2 py-1 focus-visible:outline-2 focus-visible:outline-white">
                <img src="/img/logo-ipuc.webp" alt="" width={58} height={40} className="h-10 w-auto" />
                <span className="text-lg leading-tight font-bold tracking-wide">
                    {site?.name ?? 'Comités IPUC'}
                    {site?.city && <span className="block text-base font-normal text-white/80">{site.city}</span>}
                </span>
            </Link>

            <ul className="flex flex-col gap-1">
                {navItems(props).map((item) => (
                    <SideItem key={item.href} item={item} active={url.startsWith(item.href)} />
                ))}
            </ul>
        </nav>
    );
}

function SideItem({ item, active }: { item: MenuItem; active: boolean }) {
    const Icon = item.icon;

    return (
        <li>
            <Link
                href={item.href}
                aria-current={active ? 'page' : undefined}
                className={`flex h-14 items-center gap-3 rounded-xl px-4 text-lg focus-visible:outline-2 focus-visible:outline-white ${
                    active ? 'bg-white font-bold text-ipuc' : 'text-white/90 hover:bg-white/10'
                }`}
            >
                <Icon aria-hidden className="size-6 shrink-0" />
                <span className="flex-1">{item.label}</span>
                {item.badge ? (
                    <span className="flex h-7 min-w-7 items-center justify-center rounded-full bg-ipuc-dorado px-2 text-base font-bold text-tinta">
                        {item.badge}
                        <span className="sr-only"> esperando su decisión</span>
                    </span>
                ) : null}
            </Link>
        </li>
    );
}
