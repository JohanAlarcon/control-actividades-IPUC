import { navItems } from '@/lib/navigation';
import type { SharedProps } from '@/types/shared';
import { describe, expect, it } from 'vitest';

function props(isPastor: boolean, role: 'president' | 'leader' | null, pendingCount?: number) {
    return {
        auth: { user: { id: 1, name: 'Hermano Andrés Molina', username: 'lider.sonido', isPastor }, roles: [] },
        scope: {
            activeRole: role ? { id: 1, committeeId: 1, committeeName: 'Comité de Alabanza', role } : null,
            label: null,
            mustChoose: false,
            canSwitch: false,
        },
        pendingCount,
    } satisfies Pick<SharedProps, 'auth' | 'scope' | 'pendingCount'>;
}

const labels = (items: ReturnType<typeof navItems>) => items.map((item) => item.label);

describe('navItems', () => {
    it('el pastor aprueba y administra las cuentas', () => {
        expect(labels(navItems(props(true, null)))).toEqual(['Inicio', 'Aprobar', 'Comités', 'Cuentas']);
    });

    it('un presidente revisa, no aprueba, y no administra cuentas', () => {
        expect(labels(navItems(props(false, 'president')))).toEqual(['Inicio', 'Revisar', 'Comités']);
    });

    it('un líder no aprueba: ve en qué van sus solicitudes', () => {
        expect(labels(navItems(props(false, 'leader')))).toEqual(['Inicio', 'Solicitudes', 'Comités']);
    });

    it('el número de lo que espera va sobre las solicitudes, no sobre el inicio', () => {
        const items = navItems(props(false, 'president', 3));

        expect(items.find((item) => item.href === '/approvals')?.badge).toBe(3);
        expect(items.find((item) => item.href === '/dashboard')?.badge).toBeUndefined();
    });
});
