import FlashMessages from '@/components/flash-messages';
import BottomNav from '@/components/layout/bottom-nav';
import PageTitle from '@/components/layout/page-title';
import ScopeRibbon from '@/components/layout/scope-ribbon';
import TopBar from '@/components/layout/top-bar';
import UpdateBanner from '@/components/layout/update-banner';
import type { SharedProps } from '@/types/shared';
import { Head, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';

interface Props {
    title: string;
    subtitle?: string;
    back?: { href: string; label: string };
    children: ReactNode;
}

/**
 * El molde de toda pantalla interna. Arriba, sobre el azul de la iglesia, la barra, con qué
 * cargo está trabajando y el título; el contenido sube encima como una hoja; y abajo, el menú,
 * siempre a la vista y al alcance del pulgar. En una página de error puede no haber sesión
 * todavía: entonces quedan solo la barra y el título.
 */
export default function MainLayout({ title, subtitle, back, children }: Props) {
    const { auth } = usePage<Partial<SharedProps>>().props;
    const user = auth?.user ?? null;

    return (
        <div className="min-h-screen bg-fondo text-tinta">
            <Head title={title} />

            <header className="bg-linear-to-br from-ipuc to-ipuc-oscuro pb-10 text-white">
                <div className="mx-auto max-w-2xl px-4">
                    <TopBar user={user} />
                    {user && <ScopeRibbon />}
                    <PageTitle title={title} subtitle={subtitle} back={back} />
                </div>
            </header>

            <main className="relative -mt-6 rounded-t-3xl bg-fondo">
                <div className={`mx-auto max-w-2xl px-4 pt-6 ${user ? 'pb-28' : 'pb-10'}`}>
                    {user && <FlashMessages />}
                    {children}
                </div>
            </main>

            {user && <UpdateBanner />}
            {user && <BottomNav />}
        </div>
    );
}
