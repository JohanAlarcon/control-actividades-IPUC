import type { CommitteeOption } from '@/components/sympathizers/committee-choice';
import { shortDate } from '@/lib/format';
import { buttonClass, cardClass, errorClass, fieldClass } from '@/lib/styles';
import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';

export interface AssignmentItem {
    committee: string;
    by: string | null;
    at: string;
}

interface Props {
    sympathizerId: number;
    committee: string | null;
    canAssign: boolean;
    /** Llegan aparte, y solo a BIS y al pastor (excepción 2). */
    committees?: CommitteeOption[] | null;
    assignments?: AssignmentItem[];
}

/**
 * Qué comité la acompaña y por cuáles pasó (RF-097). BIS la reasigna: deja de verla el comité
 * anterior, la ve el nuevo con toda su historia, y el cambio queda escrito (HU-14 escenario 5).
 */
export default function AssignmentPanel({ sympathizerId, committee, canAssign, committees, assignments }: Props) {
    const { errors } = usePage<{ errors: Record<string, string> }>().props;
    const [target, setTarget] = useState<number | ''>('');
    const [busy, setBusy] = useState(false);

    function reassign() {
        if (!target) return;
        setBusy(true);
        router.post(`/sympathizers/${sympathizerId}/assign`, { committee_id: target }, { preserveScroll: true, onFinish: () => setBusy(false), onSuccess: () => setTarget('') });
    }

    return (
        <section aria-labelledby="quien-acompana" className={`mb-6 p-4 ${cardClass}`}>
            <h2 id="quien-acompana" className="text-lg text-tinta-suave">
                La acompaña
            </h2>
            <p className="text-xl font-bold break-words">{committee ?? 'Sin comité'}</p>

            {canAssign && (
                <div className="mt-4 flex flex-col gap-3">
                    <label htmlFor="reasignar" className="text-lg font-semibold">
                        Pasarla a otro comité
                    </label>
                    <select id="reasignar" value={target} disabled={!committees} onChange={(event) => setTarget(event.target.value ? Number(event.target.value) : '')} className={fieldClass}>
                        <option value="">{committees ? 'Escoja el comité' : 'Cargando los comités…'}</option>
                        {committees?.map((option) => (
                            <option key={option.id} value={option.id}>
                                {option.name}
                            </option>
                        ))}
                    </select>
                    {errors.committee_id && <p className={errorClass}>{errors.committee_id}</p>}
                    <button type="button" onClick={reassign} disabled={!target || busy} className={buttonClass.secondary}>
                        Reasignarla
                    </button>
                </div>
            )}

            {assignments && assignments.length > 1 && (
                <ol className="mt-4 flex flex-col gap-1 border-t border-ipuc/10 pt-3 text-lg">
                    {assignments.map((item, index) => (
                        <li key={`${item.at}-${index}`} className="text-tinta-suave">
                            {shortDate(item.at)}: {item.committee}
                            {item.by ? `, por ${item.by}` : ', al cerrarse su comité'}
                        </li>
                    ))}
                </ol>
            )}
        </section>
    );
}
