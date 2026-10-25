import TextField from '@/components/text-field';
import HandoverQuestion from '@/components/users/handover-question';
import RolePicker from '@/components/users/role-picker';
import { buttonClass } from '@/lib/styles';
import type { CommitteeOption, EditableAccount } from '@/types/accounts';
import { useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface Props {
    account: EditableAccount | null;
    committees: CommitteeOption[];
}

export default function AccountForm({ account, committees }: Props) {
    const form = useForm({
        name: account?.name ?? '',
        username: account?.username ?? '',
        committee_id: '',
        role: '',
        handover: false,
    });

    const send = (handover: boolean) => {
        form.transform((data) => ({ ...data, handover }));

        if (account) {
            form.patch(`/users/${account.id}`);
        } else {
            form.post('/users');
        }
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        send(false);
    };

    const pick = (committeeId: string, role: string) => {
        form.clearErrors('handover');
        form.setData((data) => ({ ...data, committee_id: committeeId, role }));
    };

    return (
        <form onSubmit={submit} className="flex flex-col gap-5">
            <TextField
                id="name"
                label="Nombre completo"
                value={form.data.name}
                onChange={(e) => form.setData('name', e.target.value)}
                error={form.errors.name}
                autoComplete="off"
                autoFocus={!account}
            />
            <TextField
                id="username"
                label="Usuario"
                value={form.data.username}
                onChange={(e) => form.setData('username', e.target.value.toLowerCase())}
                error={form.errors.username}
                hint={account ? undefined : 'Si lo deja vacío, el sistema lo arma con el nombre. Por ejemplo: rigoberto.paez'}
                autoCapitalize="none"
                autoCorrect="off"
                spellCheck={false}
            />

            {!account && (
                <RolePicker
                    optional
                    committees={committees}
                    committeeId={form.data.committee_id}
                    role={form.data.role}
                    errors={form.errors}
                    onChange={pick}
                />
            )}

            {form.errors.handover ? (
                <HandoverQuestion
                    message={form.errors.handover}
                    sending={form.processing}
                    onConfirm={() => send(true)}
                    onCancel={() => form.clearErrors('handover')}
                />
            ) : (
                <button type="submit" disabled={form.processing} className={buttonClass.primary}>
                    {account ? 'Guardar los cambios' : 'Crear la cuenta'}
                </button>
            )}
        </form>
    );
}
