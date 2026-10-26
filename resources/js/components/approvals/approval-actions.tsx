import { buttonClass, errorClass, textAreaClass } from '@/lib/styles';
import type { ApprovalItem } from '@/types/approvals';
import { useForm } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';

/** El presidente revisa: da su visto bueno o la devuelve. El pastor aprueba o no (D-24). */
const WORDS = {
    president: {
        yes: 'Visto bueno',
        no: 'Devolver',
        question: '¿Por qué la devuelve?',
        reader: 'va a leer este motivo y podrá corregirla.',
        hint: 'Si le da el visto bueno, pasa al pastor, que es quien aprueba.',
    },
    pastor: { yes: 'Aprobar', no: 'No aprobar', question: '¿Por qué no la aprueba?', reader: 'va a leer este motivo.', hint: null },
};

/** Los dos botones de la tarjeta. Devolver o no aprobar piden el motivo antes de enviar (RF-020). */
export default function ApprovalActions({ item }: { item: ApprovalItem }) {
    const [rejecting, setRejecting] = useState(false);
    const form = useForm({ reason: '' });
    const reasonId = `motivo-${item.id}`;
    const words = WORDS[item.level];

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
                    {words.question}
                </label>
                <p className="text-lg text-tinta-suave">
                    {item.requestedBy} {words.reader}
                </p>
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
                        {words.no}
                    </button>
                </div>
            </form>
        );
    }

    return (
        <div className="mt-4 border-t border-ipuc/10 pt-4">
            {words.hint && <p className="mb-3 text-lg text-tinta-suave">{words.hint}</p>}
            <div className="grid grid-cols-2 gap-3">
                <button type="button" onClick={() => setRejecting(true)} className={buttonClass.secondary}>
                    {words.no}
                </button>
                <button type="button" onClick={approve} disabled={form.processing} className={buttonClass.primary}>
                    {words.yes}
                </button>
            </div>
        </div>
    );
}
