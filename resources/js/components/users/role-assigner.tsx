import HandoverQuestion from '@/components/users/handover-question';
import RolePicker from '@/components/users/role-picker';
import { buttonClass } from '@/lib/styles';
import type { CommitteeOption } from '@/types/accounts';
import { useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface Props {
    userId: number;
    committees: CommitteeOption[];
    personName: string;
}

export default function RoleAssigner({ userId, committees, personName }: Props) {
    const form = useForm({ user_id: userId, committee_id: '', role: '', handover: false });

    const send = (handover: boolean) => {
        form.transform((data) => ({ ...data, handover }));
        form.post(`/committees/${form.data.committee_id}/roles`, {
            preserveScroll: true,
            onSuccess: () => form.reset('committee_id', 'role'),
        });
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
        <form onSubmit={submit} className="mt-6 flex flex-col gap-5 border-t border-ipuc/10 pt-6">
            <h3 className="text-lg font-bold">Asignar otro cargo</h3>
            <RolePicker committees={committees} committeeId={form.data.committee_id} role={form.data.role} errors={form.errors} onChange={pick} personName={personName} />

            {form.errors.handover ? (
                <HandoverQuestion
                    message={form.errors.handover}
                    sending={form.processing}
                    onConfirm={() => send(true)}
                    onCancel={() => form.clearErrors('handover')}
                />
            ) : (
                <button type="submit" disabled={form.processing || !form.data.role} className={buttonClass.primary}>
                    Asignar el cargo
                </button>
            )}
        </form>
    );
}
