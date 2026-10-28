import { decidesApprovals } from '@/lib/roles';
import type { SharedProps } from '@/types/shared';
import { CalendarDays, HeartHandshake, House, Inbox, Network, Quote, UserRound, UsersRound, type LucideIcon } from 'lucide-react';

export interface MenuItem {
    href: string;
    label: string;
    icon: LucideIcon;
    badge?: number;
    /** Si va también en el menú de abajo del celular. */
    onPhone: boolean;
}

/**
 * Las secciones del menú. Al costado, en el escritorio, van todas. Abajo, en el celular, caben
 * tres con la palabra completa a 18 px: Inicio, Cronograma y lo más usado del cargo —la bandeja
 * para quien decide, los comités para los demás—. Lo que no cabe queda en los atajos del inicio.
 * El número va sobre las solicitudes: lo que espera la decisión del cargo (RF-055).
 */
export function navItems({ auth, scope, pendingCount }: Pick<SharedProps, 'auth' | 'scope' | 'pendingCount'>): MenuItem[] {
    const isPastor = Boolean(auth.user?.isPastor);
    const decides = decidesApprovals(auth.user?.isPastor, scope.activeRole?.role);

    return [
        { href: '/dashboard', label: 'Inicio', icon: House, onPhone: true },
        { href: '/activities', label: 'Cronograma', icon: CalendarDays, onPhone: true },
        { href: '/approvals', label: isPastor ? 'Aprobar' : decides ? 'Revisar' : 'Solicitudes', icon: Inbox, badge: pendingCount, onPhone: decides },
        { href: '/committees', label: 'Comités', icon: Network, onPhone: !decides },
        { href: '/members', label: 'Miembros', icon: UserRound, onPhone: false },
        ...(auth.followsSympathizers ? [{ href: '/sympathizers', label: 'Simpatizantes', icon: HeartHandshake, onPhone: false }] : []),
        ...(isPastor ? [{ href: '/users', label: 'Cuentas', icon: UsersRound, onPhone: false }] : []),
        ...(isPastor ? [{ href: '/site-motto', label: 'Lema del año', icon: Quote, onPhone: false }] : []),
    ];
}
