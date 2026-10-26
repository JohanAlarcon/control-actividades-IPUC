import InitialsAvatar from '@/components/initials-avatar';
import { ROLE_LABELS } from '@/lib/roles';
import { cardClass } from '@/lib/styles';
import type { Account } from '@/types/accounts';
import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';

export default function AccountCard({ account }: { account: Account }) {
    return (
        <li>
            <Link
                href={`/users/${account.id}/edit`}
                className={`flex items-center gap-4 p-4 hover:ring-2 hover:ring-ipuc/15 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ipuc active:bg-ipuc/5 ${cardClass}`}
            >
                <InitialsAvatar name={account.name} tone={account.active ? 'brand' : 'muted'} />

                <div className="min-w-0 flex-1 break-words">
                    <p className="text-lg leading-snug font-bold">{account.name}</p>
                    <p className="text-lg text-tinta-suave">{account.username}</p>
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

function Roles({ account }: { account: Account }) {
    if (account.isPastor) {
        return <p className="mt-1 text-lg font-semibold text-ipuc">Pastor de la congregación</p>;
    }

    if (account.roles.length === 0) {
        return <p className="mt-1 text-lg text-tinta-suave">Sin cargo asignado</p>;
    }

    return (
        <ul className="mt-1 text-lg">
            {account.roles.map((cargo) => (
                <li key={cargo.id}>
                    <span className="font-semibold text-ipuc">{ROLE_LABELS[cargo.role]}</span> · {cargo.committeeName}
                </li>
            ))}
        </ul>
    );
}
