import { navItems, type MenuItem } from '@/lib/navigation';
import type { SharedProps } from '@/types/shared';
import { Link, usePage } from '@inertiajs/react';
import { LogOut, PanelLeftClose, PanelLeftOpen } from 'lucide-react';

interface Props {
    collapsed: boolean;
    onToggle: () => void;
}

const itemClass = 'relative flex w-full items-center gap-3 rounded-xl text-lg focus-visible:outline-2 focus-visible:outline-white';

/**
 * El menú en el escritorio, de 1024 px en adelante: al costado, con el logo arriba y «Salir» al
 * final. Se encoge a solo los iconos para dar más ancho al contenido (RF-121). Trae las mismas
 * secciones que el menú de abajo del celular, que en esa medida se esconde.
 */
export default function SideNav({ collapsed, onToggle }: Props) {
    const { props, url } = usePage<SharedProps>();
    const site = props.site;
    const ToggleIcon = collapsed ? PanelLeftOpen : PanelLeftClose;
    // Encogido, sin palabras que se partan en dos renglones, cada botón cabe en 48 px.
    const spacing = collapsed ? 'h-12 justify-center' : 'h-14 px-4';

    return (
        <nav
            aria-label="Menú principal"
            className={`fixed inset-y-0 left-0 z-10 hidden flex-col gap-6 bg-ipuc-oscuro py-5 text-white lg:flex ${collapsed ? 'w-20 px-3' : 'w-72 px-4'}`}
        >
            <Link
                href="/dashboard"
                aria-label={collapsed ? 'Inicio' : undefined}
                className="flex items-center gap-3 rounded-xl px-1 py-1 focus-visible:outline-2 focus-visible:outline-white"
            >
                <img src="/img/logo-ipuc.webp" alt="" width={58} height={40} className={collapsed ? 'h-8 w-auto' : 'h-10 w-auto'} />
                {!collapsed && (
                    <span className="text-lg leading-tight font-bold tracking-wide">
                        {site?.name ?? 'Comités IPUC'}
                        {site?.city && <span className="block text-base font-normal text-white/80">{site.city}</span>}
                    </span>
                )}
            </Link>

            {/* Solo la lista se desplaza si la pantalla es baja: el logo, encoger y «Salir» siempre se ven. */}
            <ul className="-mx-1 flex min-h-0 flex-1 flex-col gap-1 overflow-y-auto px-1 py-1 [scrollbar-width:thin]">
                {navItems(props).map((item) => (
                    <SideItem key={item.href} item={item} active={url.startsWith(item.href)} collapsed={collapsed} />
                ))}
            </ul>

            <div className="flex shrink-0 flex-col gap-1 border-t border-white/15 pt-4">
                <button
                    type="button"
                    onClick={onToggle}
                    aria-expanded={!collapsed}
                    title={collapsed ? 'Abrir el menú' : undefined}
                    className={`${itemClass} ${spacing} text-white/90 hover:bg-white/10`}
                >
                    <ToggleIcon aria-hidden className="size-6 shrink-0" />
                    <span className={collapsed ? 'sr-only' : ''}>{collapsed ? 'Abrir el menú' : 'Encoger el menú'}</span>
                </button>
                <Link href="/logout" method="post" as="button" title={collapsed ? 'Salir' : undefined} className={`${itemClass} ${spacing} text-white/90 hover:bg-white/10`}>
                    <LogOut aria-hidden className="size-6 shrink-0" />
                    <span className={collapsed ? 'sr-only' : ''}>Salir</span>
                </Link>
            </div>
        </nav>
    );
}

function SideItem({ item, active, collapsed }: { item: MenuItem; active: boolean; collapsed: boolean }) {
    const Icon = item.icon;

    return (
        <li>
            <Link
                href={item.href}
                aria-current={active ? 'page' : undefined}
                title={collapsed ? item.label : undefined}
                className={`${itemClass} ${collapsed ? 'h-12 justify-center' : 'h-14 px-4'} ${active ? 'bg-white font-bold text-ipuc' : 'text-white/90 hover:bg-white/10'}`}
            >
                <Icon aria-hidden className="size-6 shrink-0" />
                <span className={collapsed ? 'sr-only' : 'flex-1'}>{item.label}</span>
                {item.badge ? (
                    <span
                        className={
                            collapsed
                                ? 'absolute top-2 right-2 size-3 rounded-full bg-ipuc-dorado'
                                : 'flex h-7 min-w-7 items-center justify-center rounded-full bg-ipuc-dorado px-2 text-base font-bold text-tinta'
                        }
                    >
                        {!collapsed && item.badge}
                        <span className="sr-only"> {collapsed ? `${item.badge} ` : ''}esperando su decisión</span>
                    </span>
                ) : null}
            </Link>
        </li>
    );
}
