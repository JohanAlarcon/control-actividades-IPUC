import SectionBlock from '@/components/section-block';
import AccountAccess from '@/components/users/account-access';
import AccountForm from '@/components/users/account-form';
import CurrentRoles from '@/components/users/current-roles';
import RoleAssigner from '@/components/users/role-assigner';
import MainLayout from '@/layouts/main-layout';
import { cardClass } from '@/lib/styles';
import type { CommitteeOption, CurrentRole, EditableAccount } from '@/types/accounts';

interface Props {
    account: EditableAccount | null;
    roles: CurrentRole[];
    committees: CommitteeOption[];
}

export default function UserForm({ account, roles, committees }: Props) {
    return (
        <MainLayout
            title={account?.name ?? 'Crear una cuenta'}
            subtitle={account ? `Usuario: ${account.username}` : 'El sistema le da un usuario y una clave temporal'}
            back={{ href: '/users', label: 'Cuentas' }}
        >
            <div className="lg:grid lg:grid-cols-2 lg:items-start lg:gap-6">
                <SectionBlock title="Datos">
                    <div className={`p-4 ${cardClass}`}>
                        <AccountForm account={account} committees={committees} />
                    </div>
                </SectionBlock>

                {account && (
                    <div>
                        <SectionBlock title="Cargos">
                            <div className={`p-4 ${cardClass}`}>
                                {account.isPastor ? (
                                    <p className="text-lg text-tinta-suave">El pastor ve toda la congregación: no necesita cargo en un comité.</p>
                                ) : (
                                    <>
                                        <CurrentRoles roles={roles} committees={committees} personName={account.name} />
                                        <RoleAssigner userId={account.id} committees={committees} personName={account.name} />
                                    </>
                                )}
                            </div>
                        </SectionBlock>

                        <SectionBlock title="Acceso">
                            <div className={`p-4 ${cardClass}`}>
                                <AccountAccess account={account} />
                            </div>
                        </SectionBlock>
                    </div>
                )}
            </div>
        </MainLayout>
    );
}
