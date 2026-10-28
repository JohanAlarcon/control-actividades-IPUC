import TextField from '@/components/text-field';
import { MONTHS } from '@/lib/schedule';
import { buttonClass, cardClass, errorClass, fieldClass } from '@/lib/styles';
import type { Highlight } from '@/types/schedule';
import { useForm } from '@inertiajs/react';
import { StickyNote } from 'lucide-react';
import { useState, type FormEvent } from 'react';

const TONES: { value: Highlight | ''; label: string }[] = [
    { value: '', label: 'Como las demás' },
    { value: 'special_day', label: 'De la sede, en rojo' },
    { value: 'district', label: 'Del distrito, en naranja' },
    { value: 'national', label: 'Nacional, en naranja' },
];

/**
 * Para el pastor: una nota general para el mes (RF-118), como una campaña de todo el mes o un
 * evento con la fecha pendiente. Se abre al tocarla, para no ocupar el celular.
 */
export default function MonthNoteForm({ year, month }: { year: number; month: number }) {
    const [open, setOpen] = useState(false);
    const form = useForm<{ year: number; month: number; text: string; highlight: Highlight | '' }>({ year, month, text: '', highlight: '' });

    function submit(event: FormEvent) {
        event.preventDefault();
        form.transform((data) => ({ ...data, year, month }));
        form.post('/month-notes', { preserveScroll: true, onSuccess: () => { form.reset('text', 'highlight'); setOpen(false); } });
    }

    if (!open) {
        return (
            <button type="button" onClick={() => setOpen(true)} className={`${buttonClass.secondary} mt-6`}>
                <StickyNote aria-hidden className="mr-2 size-6" />
                Agregar una nota a {MONTHS[month - 1].toLowerCase()}
            </button>
        );
    }

    return (
        <form onSubmit={submit} noValidate className={`mt-6 flex flex-col gap-4 p-4 ${cardClass}`}>
            <TextField id="nota-del-mes" label={`Nota de ${MONTHS[month - 1].toLowerCase()}`} hint="Por ejemplo: Operación rescate, Comité de Jóvenes." value={form.data.text} onChange={(event) => form.setData('text', event.target.value)} error={form.errors.text} />
            <div>
                <label htmlFor="nota-tono" className="block text-lg font-semibold">
                    Cómo sale
                </label>
                <select id="nota-tono" value={form.data.highlight} onChange={(event) => form.setData('highlight', event.target.value as Highlight | '')} className={fieldClass}>
                    {TONES.map((tone) => (
                        <option key={tone.value} value={tone.value}>
                            {tone.label}
                        </option>
                    ))}
                </select>
                {form.errors.highlight && <p className={errorClass}>{form.errors.highlight}</p>}
            </div>
            <div className="grid gap-3 sm:grid-cols-2">
                <button type="submit" disabled={form.processing} className={buttonClass.primary}>
                    Guardar la nota
                </button>
                <button type="button" onClick={() => setOpen(false)} className={buttonClass.secondary}>
                    Cancelar
                </button>
            </div>
        </form>
    );
}
