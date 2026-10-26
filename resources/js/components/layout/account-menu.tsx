import InitialsAvatar from '@/components/initials-avatar';
import { Link } from '@inertiajs/react';
import { KeyRound, LogOut } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

interface Props {
    user: { name: string; username: string };
}

const item =
    'flex h-14 w-full items-center gap-3 rounded-xl px-3 text-left text-lg font-semibold hover:bg-fondo focus-visible:bg-fondo focus-visible:outline-2 focus-visible:outline-ipuc';

/**
 * «Mi cuenta» abre una tarjeta pequeña debajo del botón, encima de la pantalla y sin empujarla.
 * Se cierra al tocar afuera o con Escape.
 */
export default function AccountMenu({ user }: Props) {
    const [open, setOpen] = useState(false);
    const box = useRef<HTMLDivElement>(null);
    const trigger = useRef<HTMLButtonElement>(null);

    useEffect(() => {
        if (!open) {
            return;
        }

        const tapOutside = (event: PointerEvent) => {
            if (!box.current?.contains(event.target as Node)) {
                setOpen(false);
            }
        };
        const escape = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                setOpen(false);
                trigger.current?.focus();
            }
        };
        document.addEventListener('pointerdown', tapOutside);
        document.addEventListener('keydown', escape);

        return () => {
            document.removeEventListener('pointerdown', tapOutside);
            document.removeEventListener('keydown', escape);
        };
    }, [open]);

    return (
        <div ref={box} className="relative ml-auto shrink-0">
            <button
                ref={trigger}
                type="button"
                onClick={() => setOpen(!open)}
                aria-expanded={open}
                aria-controls="mi-cuenta"
                className="flex h-12 items-center gap-2 rounded-full bg-white/10 py-1 pr-4 pl-2 text-lg whitespace-nowrap ring-1 ring-white/20 focus-visible:outline-2 focus-visible:outline-white aria-expanded:bg-white/20"
            >
                <InitialsAvatar name={user.name} size="sm" tone="light" />
                Mi cuenta
            </button>

            {open && <AccountCard user={user} />}
        </div>
    );
}

function AccountCard({ user }: Props) {
    return (
        <div
            id="mi-cuenta"
            className="absolute top-full right-0 z-50 mt-2 w-72 origin-top-right overflow-hidden rounded-2xl bg-white text-tinta shadow-flotante animate-in fade-in zoom-in-95 motion-reduce:animate-none"
        >
            <div className="flex items-center gap-3 bg-fondo p-4">
                <InitialsAvatar name={user.name} size="md" />
                <div className="min-w-0">
                    <p className="text-lg leading-snug font-bold break-words">{user.name}</p>
                    <p className="text-lg text-tinta-suave break-words">Usuario: {user.username}</p>
                </div>
            </div>

            <div className="flex flex-col gap-1 p-2">
                <Link href="/password/change" className={item}>
                    <span className="flex size-10 shrink-0 items-center justify-center rounded-full bg-ipuc/10 text-ipuc">
                        <KeyRound aria-hidden className="size-5" />
                    </span>
                    Cambiar mi clave
                </Link>
                <Link href="/logout" method="post" as="button" className={`${item} text-alerta`}>
                    <span className="flex size-10 shrink-0 items-center justify-center rounded-full bg-alerta/10">
                        <LogOut aria-hidden className="size-5" />
                    </span>
                    Salir
                </Link>
            </div>
        </div>
    );
}
