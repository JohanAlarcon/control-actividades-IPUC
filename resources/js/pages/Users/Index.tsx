import PageHeader from '@/components/page-header';
import AccountCard from '@/components/users/account-card';
import AccountSearch from '@/components/users/account-search';
import FlashMessages from '@/components/flash-messages';
import { ROLE_LABELS } from '@/lib/roles';
import { matchesEveryWord } from '@/lib/search';
import { buttonClass } from '@/lib/styles';
import type { Account } from '@/types/accounts';
import { Head, Link, useRemember } from '@inertiajs/react';
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

    return (
        <div className="min-h-screen bg-white text-tinta">
            <Head title="Cuentas" />
            <PageHeader title="Cuentas" backHref="/dashboard" backLabel="Inicio" />

            <main className="mx-auto max-w-2xl px-4 py-6">
                <FlashMessages />

                <Link href="/users/create" className={buttonClass.primary}>
                    <UserPlus aria-hidden className="mr-2 size-6" />
                    Crear una cuenta
                </Link>

                <div className="mt-6">
                    <AccountSearch value={query} onChange={setQuery} />
                    <p aria-live="polite" className="mt-2 text-lg text-tinta-suave">
                        {searching && `${found.length} de ${accounts.length} cuentas`}
                    </p>
                </div>

                {searching && found.length === 0 && (
                    <p className="mt-2 rounded-lg border border-linea/40 p-4 text-lg">
                        No hay ninguna cuenta con «{query.trim()}». Revise cómo lo escribió, o pruebe con otra palabra.
                    </p>
                )}

                <AccountGroup title="Con acceso" accounts={found.filter((account) => account.active)} />
                <AccountGroup
                    title="Sin acceso"
                    hint="Sus nombres se conservan en la historia."
                    accounts={found.filter((account) => !account.active)}
                />
            </main>
        </div>
    );
}

function AccountGroup({ title, hint, accounts }: { title: string; hint?: string; accounts: Account[] }) {
    if (accounts.length === 0) return null;

    return (
        <section className="mt-6">
            <h2 className="text-xl font-bold">
                {title} ({accounts.length})
            </h2>
            {hint && <p className="mt-1 text-lg text-tinta-suave">{hint}</p>}
            <ul className="mt-3 flex flex-col gap-3">
                {accounts.map((account) => (
                    <AccountCard key={account.id} account={account} />
                ))}
            </ul>
        </section>
    );
}
