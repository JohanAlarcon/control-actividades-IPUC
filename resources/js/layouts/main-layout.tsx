import FlashMessages from '@/components/flash-messages';
import BottomNav from '@/components/layout/bottom-nav';
import ScopeRibbon from '@/components/layout/scope-ribbon';
import TopBar from '@/components/layout/top-bar';
import type { SharedProps } from '@/types/shared';
import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';

/**
 * El molde de toda pantalla interna (T044, como en el prototipo): arriba la barra, debajo con
 * qué cargo está trabajando, y abajo el menú, siempre a la vista y al alcance del pulgar.
 * En una página de error puede no haber sesión todavía: entonces queda solo la barra.
 */
export default function MainLayout({ children }: { children: ReactNode }) {
    const { auth } = usePage<Partial<SharedProps>>().props;
    const user = auth?.user ?? null;

    return (
        <div className={`min-h-screen bg-white text-tinta ${user ? 'pb-24' : ''}`}>
            <TopBar user={user} />
            {user && <ScopeRibbon />}

            <main className="mx-auto max-w-2xl px-4 py-6">
                {user && <FlashMessages />}
                {children}
            </main>

            {user && <BottomNav />}
        </div>
    );
}
