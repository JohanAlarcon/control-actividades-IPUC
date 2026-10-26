import AccountCard from '@/components/users/account-card';
import AccountSearch from '@/components/users/account-search';
import MainLayout from '@/layouts/main-layout';
import { ROLE_LABELS } from '@/lib/roles';
import { matchesEveryWord } from '@/lib/search';
import { buttonClass } from '@/lib/styles';
import type { Account } from '@/types/accounts';
import { Link, useRemember } from '@inertiajs/react';
import { UserPlus } from 'lucide-react';

/** Todo lo que se puede buscar de una cuenta: el nombre, el usuario, sus cargos y sus comités. */
function searchableText(account: Account): string {
    const cargos = account.roles.map((cargo) => `${ROLE_LABELS[cargo.role]} ${cargo.committeeName ?? ''}`);

    return [account.name, account.username, account.isPastor ? 'pastor' : '', ...cargos].join(' ');
}

export default function UsersIndex({ accounts }: { accounts: Account[] }) {
    // Se recuerda al volver atrás: quien abre una ficha y regresa no tiene que escribir otra vez.
    const [query, setQuery] = useRemember('', 'buscar-cuentas');
    const searching = query.trim() !== '';

    const found = accounts.filter((account) => matchesEveryWord(searchableText(account), query));
    const withoutAccess = accounts.filter((account) => !account.active).length;
    const subtitle = `${accounts.length - withoutAccess} con acceso${withoutAccess > 0 ? ` · ${withoutAccess} sin acceso` : ''}`;

    return (
        <MainLayout title="Cuentas" subtitle={subtitle}>
            <Link href="/users/create" className={buttonClass.primary}>
                <UserPlus aria-hidden className="mr-2 size-6" />
                Crear una cuenta
            </Link>

            <div className="mt-4">
                <AccountSearch value={query} onChange={setQuery} />
                <p aria-live="polite" className="mt-2 px-1 text-lg text-tinta-suave">
                    {searching && `${found.length} de ${accounts.length} cuentas`}
                </p>
            </div>

            {searching && found.length === 0 && (
                <p className="mt-2 rounded-2xl bg-white p-4 text-lg shadow-tarjeta">
                    No hay ninguna cuenta con «{query.trim()}». Revise cómo lo escribió, o pruebe con otra palabra.
                </p>
            )}

            <AccountGroup title="Con acceso" accounts={found.filter((account) => account.active)} />
            <AccountGroup title="Sin acceso" hint="Sus nombres se conservan en la historia." accounts={found.filter((account) => !account.active)} />
        </MainLayout>
    );
}

function AccountGroup({ title, hint, accounts }: { title: string; hint?: string; accounts: Account[] }) {
    if (accounts.length === 0) return null;

    return (
        <section className="mt-4">
            <h2 className="px-1 text-xl font-bold">
                {title} <span className="font-normal text-tinta-suave">({accounts.length})</span>
            </h2>
            {hint && <p className="mt-1 px-1 text-lg text-tinta-suave">{hint}</p>}
            <ul className="mt-3 flex flex-col gap-3">
                {accounts.map((account) => (
                    <AccountCard key={account.id} account={account} />
                ))}
            </ul>
        </section>
    );
}
