import PageHeader from '@/components/page-header';
import AccountForm from '@/components/users/account-form';
import CurrentRoles from '@/components/users/current-roles';
import FlashMessages from '@/components/users/flash-messages';
import RoleAssigner from '@/components/users/role-assigner';
import type { CommitteeOption, CurrentRole, EditableAccount } from '@/types/accounts';
import { Head } from '@inertiajs/react';

interface Props {
    account: EditableAccount | null;
    roles: CurrentRole[];
    committees: CommitteeOption[];
}

export default function UserForm({ account, roles, committees }: Props) {
    const title = account ? 'Editar la cuenta' : 'Crear una cuenta';

    return (
        <div className="min-h-screen bg-white text-tinta">
            <Head title={title} />
            <PageHeader title={account?.name ?? title} backHref="/users" backLabel="Cuentas" />

            <main className="mx-auto max-w-2xl px-4 py-6">
                <FlashMessages />
                <AccountForm account={account} committees={committees} />

                {account && (
                    <section aria-labelledby="cargos" className="mt-10">
                        <h2 id="cargos" className="text-xl font-bold">
                            Cargos
                        </h2>
                        <CurrentRoles roles={roles} committees={committees} />
                        <RoleAssigner userId={account.id} committees={committees} />
                    </section>
                )}
            </main>
        </div>
    );
}
