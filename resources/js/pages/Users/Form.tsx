import PageHeader from '@/components/page-header';
import AccountAccess from '@/components/users/account-access';
import AccountForm from '@/components/users/account-form';
import CurrentRoles from '@/components/users/current-roles';
import RoleAssigner from '@/components/users/role-assigner';
import MainLayout from '@/layouts/main-layout';
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
        <MainLayout>
            <Head title={title} />
            <PageHeader title={account?.name ?? title} backHref="/users" backLabel="Cuentas" />

            {account && <h2 className="mb-4 text-xl font-bold">Datos</h2>}
            <AccountForm account={account} committees={committees} />

            {account && (
                <>
                    <section aria-labelledby="cargos" className="mt-10 border-t border-linea/40 pt-8">
                        <h2 id="cargos" className="text-xl font-bold">
                            Cargos
                        </h2>
                        {account.isPastor ? (
                            <p className="mt-3 text-lg text-tinta-suave">El pastor ve toda la congregación: no necesita cargo en un comité.</p>
                        ) : (
                            <>
                                <CurrentRoles roles={roles} committees={committees} />
                                <RoleAssigner userId={account.id} committees={committees} />
                            </>
                        )}
                    </section>

                    <section aria-labelledby="acceso" className="mt-10 border-t border-linea/40 pt-8">
                        <h2 id="acceso" className="text-xl font-bold">
                            Acceso
                        </h2>
                        <AccountAccess account={account} />
                    </section>
                </>
            )}
        </MainLayout>
    );
}
