import FlashMessages from '@/components/flash-messages';
import BottomNav from '@/components/layout/bottom-nav';
import MottoLine from '@/components/layout/motto-line';
import NoticeToast from '@/components/layout/notice-toast';
import PageTitle from '@/components/layout/page-title';
import ScopeRibbon from '@/components/layout/scope-ribbon';
import SideNav from '@/components/layout/side-nav';
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

/** El ancho del contenido: el de un celular grande y, en el escritorio, casi todo lo que deja el menú. */
const container = 'mx-auto max-w-2xl px-4 lg:max-w-5xl lg:px-8';

/**
 * El molde de toda pantalla interna. Arriba, sobre el azul de la iglesia, la barra, con qué
 * cargo está trabajando y el título; el contenido sube encima como una hoja; y abajo, el menú,
 * siempre a la vista y al alcance del pulgar. En el escritorio el menú va al costado. En una
 * página de error puede no haber sesión todavía: entonces quedan solo la barra y el título.
 */
export default function MainLayout({ title, subtitle, back, children }: Props) {
    const { auth } = usePage<Partial<SharedProps>>().props;
    const user = auth?.user ?? null;

    return (
        <div className={`min-h-screen bg-fondo text-tinta ${user ? 'lg:pl-72' : ''}`}>
            <Head title={title} />
            {user && <SideNav />}

            <header className="bg-linear-to-br from-ipuc to-ipuc-oscuro pb-10 text-white">
                <div className={container}>
                    <TopBar user={user} />
                    <MottoLine />
                    {user && <ScopeRibbon />}
                    <PageTitle title={title} subtitle={subtitle} back={back} />
                </div>
            </header>

            <main className="relative -mt-6 rounded-t-3xl bg-fondo">
                <div className={`${container} pt-6 ${user ? 'pb-28 lg:pb-12' : 'pb-10'}`}>
                    {user && <FlashMessages />}
                    {children}
                </div>
            </main>

            {user && <NoticeToast />}
            {user && <UpdateBanner />}
            {user && <BottomNav />}
        </div>
    );
}
