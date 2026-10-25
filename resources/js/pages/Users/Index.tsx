import PageHeader from '@/components/page-header';
import AccountCard from '@/components/users/account-card';
import FlashMessages from '@/components/users/flash-messages';
import { buttonClass } from '@/lib/styles';
import type { Account } from '@/types/accounts';
import { Head, Link } from '@inertiajs/react';

export default function UsersIndex({ accounts }: { accounts: Account[] }) {
    const withAccess = accounts.filter((account) => account.active);
    const withoutAccess = accounts.filter((account) => !account.active);

    return (
        <div className="min-h-screen bg-white text-tinta">
            <Head title="Cuentas" />
            <PageHeader title="Cuentas" backHref="/dashboard" backLabel="Inicio" />

            <main className="mx-auto max-w-2xl px-4 py-6">
                <FlashMessages />

                <Link href="/users/create" className={buttonClass.primary}>
                    Crear una cuenta
                </Link>

                <h2 className="mt-8 text-xl font-bold">Con acceso ({withAccess.length})</h2>
                <ul className="mt-3 flex flex-col gap-3">
                    {withAccess.map((account) => (
                        <AccountCard key={account.id} account={account} />
                    ))}
                </ul>

                {withoutAccess.length > 0 && (
                    <>
                        <h2 className="mt-8 text-xl font-bold">Sin acceso ({withoutAccess.length})</h2>
                        <p className="mt-1 text-lg text-tinta-suave">Sus nombres se conservan en la historia.</p>
                        <ul className="mt-3 flex flex-col gap-3">
                            {withoutAccess.map((account) => (
                                <AccountCard key={account.id} account={account} />
                            ))}
                        </ul>
                    </>
                )}
            </main>
        </div>
    );
}
