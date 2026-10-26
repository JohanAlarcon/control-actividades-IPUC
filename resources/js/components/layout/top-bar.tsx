import InitialsAvatar from '@/components/initials-avatar';
import { buttonClass } from '@/lib/styles';
import { Link } from '@inertiajs/react';
import { useState } from 'react';

export default function TopBar({ user }: { user: { name: string } | null }) {
    const [open, setOpen] = useState(false);

    return (
        <>
            <div className="flex h-16 items-center justify-between gap-3">
                <Link href="/dashboard" className="flex items-center gap-2 rounded-lg focus-visible:outline-2 focus-visible:outline-white">
                    <img src="/img/logo-ipuc.webp" alt="" width={46} height={32} className="h-8 w-auto" />
                    {/* En un celular de menos de 360 px no cabe junto a «Mi cuenta»: queda el logo, y el nombre para los lectores de pantalla. */}
                    <span className="text-base font-bold tracking-wide max-[359px]:sr-only">Comités IPUC</span>
                </Link>

                {user && (
                    <button
                        type="button"
                        onClick={() => setOpen(!open)}
                        aria-expanded={open}
                        aria-controls="mi-cuenta"
                        className="flex h-12 items-center gap-2 rounded-full bg-white/10 py-1 pr-4 pl-2 text-lg ring-1 ring-white/20 focus-visible:outline-2 focus-visible:outline-white"
                    >
                        <InitialsAvatar name={user.name} size="sm" tone="light" />
                        Mi cuenta
                    </button>
                )}
            </div>

            {user && open && (
                <div id="mi-cuenta" className="mb-3 rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">
                    <p className="mb-3 text-lg font-semibold">{user.name}</p>
                    <div className="flex flex-col gap-2">
                        <Link href="/password/change" className={buttonClass.secondary}>
                            Cambiar mi clave
                        </Link>
                        <Link href="/logout" method="post" as="button" className={buttonClass.secondary}>
                            Salir
                        </Link>
                    </div>
                </div>
            )}
        </>
    );
}
