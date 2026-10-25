import { ROLE_LABELS } from '@/lib/roles';
import { buttonClass } from '@/lib/styles';
import type { CommitteeOption, CurrentRole } from '@/types/accounts';
import { router } from '@inertiajs/react';
import { useState } from 'react';

interface Props {
    roles: CurrentRole[];
    committees: CommitteeOption[];
}

export default function CurrentRoles({ roles, committees }: Props) {
    if (roles.length === 0) {
        return <p className="mt-3 text-lg text-tinta-suave">Todavía no tiene cargo.</p>;
    }

    const nameOf = (id: number) => committees.find((committee) => committee.id === id)?.name ?? 'Comité retirado';

    return (
        <ul className="mt-3 flex flex-col gap-3">
            {roles.map((role) => (
                <RoleRow key={role.id} role={role} committeeName={nameOf(role.committeeId)} />
            ))}
        </ul>
    );
}

function RoleRow({ role, committeeName }: { role: CurrentRole; committeeName: string }) {
    const [asking, setAsking] = useState(false);
    const [sending, setSending] = useState(false);

    const vacate = () =>
        router.delete(`/roles/${role.id}`, {
            preserveScroll: true,
            onStart: () => setSending(true),
            onFinish: () => setSending(false),
        });

    return (
        <li className="rounded-lg border-2 border-linea p-4">
            <p className="text-lg font-semibold">
                {ROLE_LABELS[role.role]} · {committeeName}
            </p>

            {asking ? (
                <div className="mt-3">
                    <p className="text-lg">El cargo queda vacante. Sus solicitudes pendientes esperan a quien lo reemplace.</p>
                    <div className="mt-3 grid gap-2 sm:grid-cols-2">
                        <button type="button" disabled={sending} onClick={vacate} className={buttonClass.danger}>
                            Sí, quitarle el cargo
                        </button>
                        <button type="button" disabled={sending} onClick={() => setAsking(false)} className={buttonClass.secondary} autoFocus>
                            No, dejarlo así
                        </button>
                    </div>
                </div>
            ) : (
                <button type="button" onClick={() => setAsking(true)} className={`mt-3 ${buttonClass.secondary}`}>
                    Quitarle el cargo
                </button>
            )}
        </li>
    );
}
