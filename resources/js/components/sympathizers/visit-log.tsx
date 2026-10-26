import ChoiceChips from '@/components/people/choice-chips';
import TextField from '@/components/text-field';
import { calendarDate, shortDate } from '@/lib/format';
import { VISIT_TYPES } from '@/lib/people';
import { buttonClass, cardClass, errorClass, textAreaClass } from '@/lib/styles';
import type { VisitItem } from '@/types/people';
import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

/**
 * La bitácora de visitas: cada visita, llamada, estudio o culto, con su fecha, quién y una nota
 * (RF-044). Al anotar una, el compromiso se recalcula solo (HU-14, escenario 4).
 */
export default function VisitLog({ sympathizerId, visits }: { sympathizerId: number; visits?: VisitItem[] }) {
    const form = useForm({ visited_at: calendarDate(new Date()), type: '', note: '' });
    const label = (type: string) => VISIT_TYPES.find((item) => item.value === type)?.label ?? type;

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post(`/sympathizers/${sympathizerId}/visits`, { preserveScroll: true, onSuccess: () => form.reset('type', 'note') });
    }

    return (
        <section aria-labelledby="visitas" className="mb-6">
            <h2 id="visitas" className="mb-3 px-1 text-xl font-bold">
                Visitas y contactos
            </h2>
            <div className={`overflow-hidden ${cardClass}`}>
                <form onSubmit={submit} noValidate className="flex flex-col gap-4 p-4">
                    <ChoiceChips id="type" legend="¿Qué fue?" options={VISIT_TYPES} value={form.data.type} onChange={(value) => form.setData('type', value)} error={form.errors.type} />
                    <TextField
                        id="visited_at"
                        type="date"
                        label="¿Cuándo?"
                        max={calendarDate(new Date())}
                        value={form.data.visited_at}
                        onChange={(event) => form.setData('visited_at', event.target.value)}
                        error={form.errors.visited_at}
                    />
                    <div>
                        <label htmlFor="note" className="block text-lg font-semibold">
                            Nota (opcional)
                        </label>
                        <textarea id="note" rows={3} value={form.data.note} onChange={(event) => form.setData('note', event.target.value)} className={textAreaClass} />
                        {form.errors.note && <p className={errorClass}>{form.errors.note}</p>}
                    </div>
                    <button type="submit" disabled={form.processing} className={buttonClass.primary}>
                        Anotar
                    </button>
                </form>

                {visits === undefined ? (
                    <p className="border-t border-ipuc/10 p-4 text-lg text-tinta-suave">Cargando…</p>
                ) : visits.length === 0 ? (
                    <p className="border-t border-ipuc/10 p-4 text-lg text-tinta-suave">Todavía no hay visitas anotadas.</p>
                ) : (
                    <ul className="divide-y divide-ipuc/5 border-t border-ipuc/10">
                        {visits.map((visit) => (
                            <li key={visit.id} className="p-4">
                                <p className="text-lg font-semibold">
                                    {label(visit.type)} · {shortDate(`${visit.visitedAt}T12:00:00-05:00`)}
                                </p>
                                {visit.by && <p className="text-lg text-tinta-suave">Por {visit.by}</p>}
                                {visit.note && <p className="mt-1 text-lg">{visit.note}</p>}
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </section>
    );
}
