import AccountMenu from '@/components/layout/account-menu';
import { Link } from '@inertiajs/react';

export default function TopBar({ user }: { user: { name: string; username: string } | null }) {
    return (
        <div className="flex h-16 items-center justify-between gap-3">
            {/* En el escritorio, con sesión, el logo va en el menú del costado. */}
            <Link
                href="/dashboard"
                className={`flex items-center gap-2 rounded-lg focus-visible:outline-2 focus-visible:outline-white ${user ? 'lg:hidden' : ''}`}
            >
                <img src="/img/logo-ipuc.webp" alt="" width={46} height={32} className="h-8 w-auto" />
                {/* En un celular de menos de 360 px no cabe junto a «Mi cuenta»: queda el logo, y el nombre para los lectores de pantalla. */}
                <span className="text-base leading-tight font-bold tracking-wide max-[359px]:sr-only">Comités IPUC</span>
            </Link>

            {user && <AccountMenu user={user} />}
        </div>
    );
}
