import { decidesApprovals } from '@/lib/roles';
import type { SharedProps } from '@/types/shared';
import { House, Inbox, Network, UsersRound, type LucideIcon } from 'lucide-react';

export interface MenuItem {
    href: string;
    label: string;
    icon: LucideIcon;
    badge?: number;
}

/**
 * Las secciones del menú: las mismas abajo en el celular y al costado en el escritorio. Solo trae
 * las que ya existen; Cronograma y Dinero se suman cuando estén. El número va sobre las
 * solicitudes: lo que espera la decisión del cargo (RF-055). El pastor aprueba; el presidente
 * revisa (D-24).
 */
export function navItems({ auth, scope, pendingCount }: Pick<SharedProps, 'auth' | 'scope' | 'pendingCount'>): MenuItem[] {
    const decides = decidesApprovals(auth.user?.isPastor, scope.activeRole?.role);

    return [
        { href: '/dashboard', label: 'Inicio', icon: House },
        { href: '/approvals', label: auth.user?.isPastor ? 'Aprobar' : decides ? 'Revisar' : 'Solicitudes', icon: Inbox, badge: pendingCount },
        { href: '/committees', label: 'Comités', icon: Network },
        ...(auth.user?.isPastor ? [{ href: '/users', label: 'Cuentas', icon: UsersRound }] : []),
    ];
}
