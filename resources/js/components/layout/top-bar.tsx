import AccountMenu from '@/components/layout/account-menu';
import type { SharedProps } from '@/types/shared';
import { Link, usePage } from '@inertiajs/react';

export default function TopBar({ user }: { user: { name: string; username: string } | null }) {
    const { site } = usePage<Partial<SharedProps>>().props;

    return (
        <div className="flex h-16 items-center justify-between gap-3">
            {/* En el escritorio, con sesión, el logo va en el menú del costado. */}
            <Link
                href="/dashboard"
                className={`flex items-center gap-2 rounded-lg focus-visible:outline-2 focus-visible:outline-white ${user ? 'lg:hidden' : ''}`}
            >
                <img src="/img/logo-ipuc.webp" alt="" width={46} height={32} className="h-8 w-auto" />
                {/* El nombre de la sede, en dos renglones si hace falta para caber junto a «Mi cuenta» (RF-081). */}
                <span className="max-w-[9rem] text-base leading-tight font-bold tracking-wide max-[359px]:sr-only">{site?.name ?? 'Comités IPUC'}</span>
            </Link>

            {user && <AccountMenu user={user} />}
        </div>
    );
}
