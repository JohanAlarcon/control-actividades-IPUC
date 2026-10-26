import { ROLE_LABELS } from '@/lib/roles';
import type { Account } from '@/types/accounts';
import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';

export default function AccountCard({ account }: { account: Account }) {
    return (
        <li>
            <Link
                href={`/users/${account.id}/edit`}
                className="flex items-center gap-4 rounded-lg border border-linea/40 bg-white p-4 shadow-sm hover:border-ipuc focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ipuc active:bg-ipuc/5"
            >
                <Initials name={account.name} muted={!account.active} />

                <div className="min-w-0 flex-1 break-words">
                    <p className="text-lg leading-snug font-bold">{account.name}</p>
                    <p className="text-lg text-tinta-suave">Usuario: {account.username}</p>
                    <Roles account={account} />
                    {account.active && account.mustChangePassword && (
                        <p className="mt-2 inline-block rounded-full bg-ipuc-dorado/20 px-3 text-lg">Tiene clave temporal</p>
                    )}
                </div>

                <ChevronRight aria-hidden className="size-6 shrink-0 text-tinta-suave" />
            </Link>
        </li>
    );
}

/** Las iniciales ayudan a encontrar a alguien de un vistazo, sin tener que leer cada nombre. */
function Initials({ name, muted }: { name: string; muted: boolean }) {
    const words = name
        .replace(/^(hermano|hermana|pastor|pastora)\s+/i, '')
        .split(/\s+/)
        .filter(Boolean);
    const letters = words.length > 1 ? words[0][0] + words[words.length - 1][0] : (words[0]?.[0] ?? '');

    return (
        <span
            aria-hidden
            className={`flex size-12 shrink-0 items-center justify-center rounded-full text-lg font-bold ${
                muted ? 'bg-linea/15 text-tinta-suave' : 'bg-ipuc/10 text-ipuc'
            }`}
        >
            {letters.toUpperCase()}
        </span>
    );
}

function Roles({ account }: { account: Account }) {
    if (account.isPastor) {
        return <p className="mt-1 text-lg">Pastor de la congregación</p>;
    }

    if (account.roles.length === 0) {
        return <p className="mt-1 text-lg text-tinta-suave">Sin cargo asignado</p>;
    }

    return (
        <ul className="mt-1 text-lg">
            {account.roles.map((cargo) => (
                <li key={cargo.id}>
                    <span className="font-semibold">{ROLE_LABELS[cargo.role]}</span> · {cargo.committeeName}
                </li>
            ))}
        </ul>
    );
}
