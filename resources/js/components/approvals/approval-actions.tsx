import { buttonClass, errorClass, textAreaClass } from '@/lib/styles';
import type { ApprovalItem } from '@/types/approvals';
import { useForm } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';

/** Aprobar o no aprobar, en la misma tarjeta. No aprobar pide el motivo antes de enviar (RF-020). */
export default function ApprovalActions({ item }: { item: ApprovalItem }) {
    const [rejecting, setRejecting] = useState(false);
    const form = useForm({ reason: '' });
    const reasonId = `motivo-${item.id}`;
    const goesToPastor = item.type === 'activity' && item.level === 'president';

    function approve() {
        form.post(`/approvals/${item.id}/approve`, { preserveScroll: true });
    }

    function reject(event: FormEvent) {
        event.preventDefault();
        form.post(`/approvals/${item.id}/reject`, { preserveScroll: true });
    }

    function cancel() {
        setRejecting(false);
        form.reset();
        form.clearErrors();
    }

    if (rejecting) {
        return (
            <form onSubmit={reject} className="mt-4 border-t border-ipuc/10 pt-4">
                <label htmlFor={reasonId} className="block text-lg font-semibold">
                    ¿Por qué no la aprueba?
                </label>
                <p className="text-lg text-tinta-suave">{item.requestedBy} va a leer este motivo.</p>
                <textarea
                    id={reasonId}
                    rows={3}
                    autoFocus
                    value={form.data.reason}
                    onChange={(event) => form.setData('reason', event.target.value)}
                    aria-invalid={form.errors.reason ? true : undefined}
                    aria-describedby={form.errors.reason ? `${reasonId}-error` : undefined}
                    className={textAreaClass}
                />
                {form.errors.reason && (
                    <p id={`${reasonId}-error`} className={errorClass}>
                        {form.errors.reason}
                    </p>
                )}
                <div className="mt-4 grid grid-cols-2 gap-3">
                    <button type="button" onClick={cancel} className={buttonClass.secondary}>
                        Volver
                    </button>
                    <button type="submit" disabled={form.processing} className={buttonClass.danger}>
                        No aprobar
                    </button>
                </div>
            </form>
        );
    }

    return (
        <div className="mt-4 border-t border-ipuc/10 pt-4">
            {goesToPastor && <p className="mb-3 text-lg text-tinta-suave">Si la aprueba, pasa al pastor, que es quien la cierra.</p>}
            <div className="grid grid-cols-2 gap-3">
                <button type="button" onClick={() => setRejecting(true)} className={buttonClass.secondary}>
                    No aprobar
                </button>
                <button type="button" onClick={approve} disabled={form.processing} className={buttonClass.primary}>
                    Aprobar
                </button>
            </div>
        </div>
    );
}
