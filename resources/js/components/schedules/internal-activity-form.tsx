import ActivityTypePicker from '@/components/activities/activity-type-picker';
import WhenFields, { type WhenField } from '@/components/activities/when-fields';
import TextField from '@/components/text-field';
import { buttonClass, errorClass, fieldClass } from '@/lib/styles';
import type { ActivityTypeOption } from '@/types/activities';
import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

type Repeat = '' | 'weekly' | 'biweekly' | 'monthly';

type InternalActivityData = Record<WhenField, string> & {
    title: string;
    activity_type_id: number | '';
    location: string;
    theme: string;
    responsible: string;
    repeat: Repeat;
    until: string;
};

interface Props {
    scheduleId: number;
    types: ActivityTypeOption[];
    /** El periodo del cronograma: la fecha se escoge dentro de él. */
    limits: { min: string; max: string };
}

/**
 * Agregar una actividad al cronograma interno (HU-27 escenarios 2 y 3). Queda en firme al guardarla:
 * no pasa por aprobación (P-03) y se puede cruzar con otras (RF-087). Si se repite, cada fecha queda
 * suelta, para moverla o cancelarla sola (D-38).
 */
export default function InternalActivityForm({ scheduleId, types, limits }: Props) {
    const form = useForm<InternalActivityData>({ title: '', activity_type_id: '', date: '', time: '', ends: '', location: '', theme: '', responsible: '', repeat: '', until: '' });

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post(`/schedules/${scheduleId}/activities`, {
            preserveScroll: true,
            onSuccess: () => form.reset(),
            onError: (found) => document.getElementById(Object.keys(found)[0])?.focus(),
        });
    }

    return (
        <form onSubmit={submit} noValidate className="flex flex-col gap-5">
            <TextField
                id="title"
                label="Nombre de la actividad"
                hint="Por ejemplo: Ensayo general."
                autoComplete="off"
                value={form.data.title}
                onChange={(event) => form.setData('title', event.target.value)}
                error={form.errors.title}
            />
            <ActivityTypePicker types={types} value={form.data.activity_type_id} onChange={(id) => form.setData('activity_type_id', id)} error={form.errors.activity_type_id} />
            <WhenFields values={form.data} limits={limits} errors={form.errors} onChange={(field, value) => form.setData(field, value)} />
            <div>
                <label htmlFor="repeat" className="block text-lg font-semibold">
                    ¿Se repite?
                </label>
                <select id="repeat" value={form.data.repeat} onChange={(event) => form.setData('repeat', event.target.value as Repeat)} className={fieldClass}>
                    <option value="">No, es una sola fecha</option>
                    <option value="weekly">Cada semana</option>
                    <option value="biweekly">Cada quince días</option>
                    <option value="monthly">Cada mes</option>
                </select>
                {form.errors.repeat && <p className={errorClass}>{form.errors.repeat}</p>}
            </div>
            {form.data.repeat && (
                <TextField
                    id="until"
                    type="date"
                    label="Hasta"
                    hint="La última fecha en que se hace."
                    min={form.data.date || limits.min}
                    max={limits.max}
                    value={form.data.until}
                    onChange={(event) => form.setData('until', event.target.value)}
                    error={form.errors.until}
                />
            )}
            <TextField id="location" label="Lugar (opcional)" value={form.data.location} onChange={(event) => form.setData('location', event.target.value)} error={form.errors.location} />
            <TextField id="theme" label="Tema (opcional)" value={form.data.theme} onChange={(event) => form.setData('theme', event.target.value)} error={form.errors.theme} />
            <TextField
                id="responsible"
                label="Quién la dirige (opcional)"
                hint="Por ejemplo: la maestra de la clase."
                value={form.data.responsible}
                onChange={(event) => form.setData('responsible', event.target.value)}
                error={form.errors.responsible}
            />
            <button type="submit" disabled={form.processing} className={buttonClass.primary}>
                Agregar al cronograma
            </button>
        </form>
    );
}
