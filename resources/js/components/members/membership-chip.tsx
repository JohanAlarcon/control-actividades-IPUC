import { functionLabel } from '@/lib/people';
import type { Gender, Membership } from '@/types/people';
import { router } from '@inertiajs/react';
import { X } from 'lucide-react';
import { useState } from 'react';

interface Props {
    memberId: number;
    memberName: string;
    gender: Gender;
    membership: Membership;
    canRemove: boolean;
}

/**
 * Una lista en la que está la persona, con su función ahí. Quien maneja la lista la puede quitar,
 * con una segunda confirmación: quitar cierra la pertenencia, no borra a nadie (RF-085).
 */
export default function MembershipChip({ memberId, memberName, gender, membership, canRemove }: Props) {
    const [asking, setAsking] = useState(false);
    const [busy, setBusy] = useState(false);
    const text = `${membership.name} · ${functionLabel(membership, gender)}`;

    if (asking) {
        return (
            <span role="group" aria-label={`Quitar a ${memberName} de ${membership.name}`} className="flex w-full flex-wrap items-center gap-2 rounded-2xl bg-alerta/10 p-2 text-lg">
                <span className="min-w-0 flex-1 px-1 font-semibold">
                    ¿Quitar a {memberName} de {membership.name}?
                </span>
                <button
                    type="button"
                    disabled={busy}
                    onClick={() => {
                        setBusy(true);
                        router.delete(`/committees/${membership.id}/members/${memberId}`, { preserveScroll: true, onFinish: () => setBusy(false) });
                    }}
                    className="h-12 rounded-xl bg-alerta px-4 font-semibold text-white disabled:opacity-60"
                >
                    Sí, quitar
                </button>
                <button type="button" onClick={() => setAsking(false)} className="h-12 rounded-xl px-4 font-semibold text-ipuc">
                    No
                </button>
            </span>
        );
    }

    return (
        <span className="inline-flex items-center gap-1 rounded-full bg-ipuc/10 py-1 pr-1 pl-3 text-lg text-ipuc">
            {text}
            {canRemove && (
                <button
                    type="button"
                    onClick={() => setAsking(true)}
                    aria-label={`Quitar a ${memberName} de la lista de ${membership.name}`}
                    className="flex size-10 items-center justify-center rounded-full hover:bg-ipuc/10 focus-visible:outline-2 focus-visible:outline-ipuc"
                >
                    <X aria-hidden className="size-5" />
                </button>
            )}
        </span>
    );
}
