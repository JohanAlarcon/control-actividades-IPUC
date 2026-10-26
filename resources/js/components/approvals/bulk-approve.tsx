import { bulkLabel, groupsForBulk } from '@/lib/approvals';
import { buttonClass, cardClass } from '@/lib/styles';
import type { ApprovalItem, ApprovalType } from '@/types/approvals';
import { router } from '@inertiajs/react';
import { CheckCheck } from 'lucide-react';
import { useState } from 'react';

type Group = { type: ApprovalType; ids: number[] };

/**
 * Aprobar varias de la misma clase en una sola acción, con una sola confirmación (HU-07,
 * escenario 4; RF-022). Solo aparece cuando hay dos o más de una misma clase.
 */
export default function BulkApprove({ items }: { items: ApprovalItem[] }) {
    const [confirming, setConfirming] = useState<Group | null>(null);
    const [busy, setBusy] = useState(false);
    const groups = groupsForBulk(items);

    if (groups.length === 0) return null;

    function approve(group: Group) {
        router.post(
            '/approvals/bulk-approve',
            { ids: group.ids },
            { preserveScroll: true, onStart: () => setBusy(true), onFinish: () => (setBusy(false), setConfirming(null)) },
        );
    }

    return (
        <div className={`mb-4 p-4 ring-1 ring-ipuc-claro/40 ${cardClass}`}>
            {confirming ? (
                <>
                    <p className="text-lg font-bold">¿{bulkLabel(confirming.type, confirming.ids.length)}?</p>
                    <p className="text-lg text-tinta-suave">Cada una queda aprobada con su propio registro, como si la aprobara sola.</p>
                    <div className="mt-4 grid grid-cols-2 gap-3">
                        <button type="button" onClick={() => setConfirming(null)} className={buttonClass.secondary}>
                            Volver
                        </button>
                        <button type="button" onClick={() => approve(confirming)} disabled={busy} className={buttonClass.primary}>
                            Sí, aprobar
                        </button>
                    </div>
                </>
            ) : (
                <>
                    <p className="flex items-center gap-2 text-lg font-bold">
                        <CheckCheck aria-hidden className="size-6 text-ipuc" />
                        Aprobar varias de una vez
                    </p>
                    <div className="mt-3 flex flex-col gap-2">
                        {groups.map((group) => (
                            <button key={group.type} type="button" onClick={() => setConfirming(group)} className={buttonClass.secondary}>
                                {bulkLabel(group.type, group.ids.length)}
                            </button>
                        ))}
                    </div>
                </>
            )}
        </div>
    );
}
