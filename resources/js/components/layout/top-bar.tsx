import { buttonClass } from '@/lib/styles';
import { Link } from '@inertiajs/react';
import { CircleUserRound } from 'lucide-react';
import { useState } from 'react';

export default function TopBar({ user }: { user: { name: string } | null }) {
    const [open, setOpen] = useState(false);

    return (
        <header className="bg-ipuc text-white">
            <div className="mx-auto flex max-w-2xl items-center justify-between gap-3 px-4">
                <Link href="/dashboard" className="flex h-14 items-center text-lg font-bold focus-visible:outline-2 focus-visible:outline-white">
                    Comités IPUC
                </Link>

                {user && (
                    <button
                        type="button"
                        onClick={() => setOpen(!open)}
                        aria-expanded={open}
                        aria-controls="mi-cuenta"
                        className="flex h-12 items-center gap-2 rounded-lg px-3 text-lg focus-visible:outline-2 focus-visible:outline-white"
                    >
                        <CircleUserRound aria-hidden className="size-6" />
                        Mi cuenta
                    </button>
                )}
            </div>

            {user && open && (
                <div id="mi-cuenta" className="bg-ipuc-oscuro">
                    <div className="mx-auto flex max-w-2xl flex-col gap-2 px-4 py-4">
                        <p className="mb-1 text-lg">{user.name}</p>
                        <Link href="/password/change" className={buttonClass.secondary}>
                            Cambiar mi clave
                        </Link>
                        <Link href="/logout" method="post" as="button" className={buttonClass.secondary}>
                            Salir
                        </Link>
                    </div>
                </div>
            )}
        </header>
    );
}
