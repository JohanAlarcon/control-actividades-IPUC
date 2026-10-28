import Pager from '@/components/pager';
import AccountCard from '@/components/users/account-card';
import AccountSearch from '@/components/users/account-search';
import MainLayout from '@/layouts/main-layout';
import { pageOf } from '@/lib/paging';
import { ROLE_LABELS, roleLabelFor } from '@/lib/roles';
import { matchesEveryWord } from '@/lib/search';
import { buttonClass } from '@/lib/styles';
import type { Account } from '@/types/accounts';
import { Link, useRemember } from '@inertiajs/react';
import { UserPlus } from 'lucide-react';

/** Todo lo que se puede buscar de una cuenta: el nombre, el usuario, sus cargos y sus comités. */
function searchableText(account: Account): string {
    const cargos = account.roles.map((cargo) => `${ROLE_LABELS[cargo.role]} ${roleLabelFor(cargo.role, account.name)} ${cargo.committeeName ?? ''}`);

    return [account.name, account.username, account.isPastor ? 'pastor' : '', ...cargos].join(' ');
}

export default function UsersIndex({ accounts }: { accounts: Account[] }) {
    // Se recuerda al volver atrás: quien abre una ficha y regresa no tiene que escribir otra vez.
    const [query, setQuery] = useRemember('', 'buscar-cuentas');
    const [page, setPage] = useRemember(1, 'pagina-cuentas');
    const searching = query.trim() !== '';

    const found = accounts.filter((account) => matchesEveryWord(searchableText(account), query));
    // Primero las que tienen acceso: la página corta la lista completa, y cada grupo muestra lo suyo de esa página.
    const ordered = [...found.filter((account) => account.active), ...found.filter((account) => !account.active)];
    const current = pageOf(ordered, page);
    const withoutAccess = accounts.filter((account) => !account.active).length;
    const subtitle = `${accounts.length - withoutAccess} con acceso${withoutAccess > 0 ? ` · ${withoutAccess} sin acceso` : ''}`;

    return (
        <MainLayout title="Cuentas" subtitle={subtitle}>
            <div className="lg:flex lg:flex-row-reverse lg:items-start lg:gap-4">
                <Link href="/users/create" className={`${buttonClass.primary} lg:w-auto lg:shrink-0 lg:px-6`}>
                    <UserPlus aria-hidden className="mr-2 size-6" />
                    Crear una cuenta
                </Link>

                <div className="mt-4 lg:mt-0 lg:flex-1">
                    <AccountSearch
                        value={query}
                        onChange={(value) => {
                            setQuery(value);
                            setPage(1);
                        }}
                    />
                    <p aria-live="polite" className="mt-2 px-1 text-lg text-tinta-suave">
                        {searching && `${found.length} de ${accounts.length} cuentas`}
                    </p>
                </div>
            </div>

            {searching && found.length === 0 && (
                <p className="mt-2 rounded-2xl bg-white p-4 text-lg shadow-tarjeta">
                    No hay ninguna cuenta con «{query.trim()}». Revise cómo lo escribió, o pruebe con otra palabra.
                </p>
            )}

            <div id="lista-cuentas" className="scroll-mt-4">
                <AccountGroup
                    title="Con acceso"
                    total={found.filter((account) => account.active).length}
                    accounts={current.items.filter((account) => account.active)}
                />
                <AccountGroup
                    title="Sin acceso"
                    hint="Sus nombres se conservan en la historia."
                    total={found.filter((account) => !account.active).length}
                    accounts={current.items.filter((account) => !account.active)}
                />
            </div>
            <Pager page={current.page} pages={current.pages} onChange={setPage} anchor="lista-cuentas" />
        </MainLayout>
    );
}

function AccountGroup({ title, hint, total, accounts }: { title: string; hint?: string; total: number; accounts: Account[] }) {
    if (accounts.length === 0) return null;

    return (
        <section className="mt-4">
            <h2 className="px-1 text-xl font-bold">
                {title} <span className="font-normal text-tinta-suave">({total})</span>
            </h2>
            {hint && <p className="mt-1 px-1 text-lg text-tinta-suave">{hint}</p>}
            <ul className="mt-3 grid gap-3 lg:grid-cols-2 lg:items-start">
                {accounts.map((account) => (
                    <AccountCard key={account.id} account={account} />
                ))}
            </ul>
        </section>
    );
}
