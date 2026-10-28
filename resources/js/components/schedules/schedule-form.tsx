import TextField from '@/components/text-field';
import { buttonClass, cardClass } from '@/lib/styles';
import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

/** Un cronograma interno nuevo (HU-27 escenario 1): su nombre y de qué fecha a qué fecha va. */
export default function ScheduleForm({ committee }: { committee: string }) {
    const form = useForm({ name: '', starts_on: '', ends_on: '' });

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post('/schedules', { onError: (found) => document.getElementById(Object.keys(found)[0])?.focus() });
    }

    return (
        <form onSubmit={submit} noValidate className={`flex flex-col gap-5 p-4 ${cardClass}`}>
            <p className="text-lg text-tinta-suave">Queda para {committee}.</p>
            <TextField
                id="name"
                label="Nombre"
                hint="Por ejemplo: Módulo 1, o Ensayos del primer semestre."
                autoComplete="off"
                value={form.data.name}
                onChange={(event) => form.setData('name', event.target.value)}
                error={form.errors.name}
            />
            <div className="grid gap-5 sm:grid-cols-2">
                <TextField id="starts_on" type="date" label="Empieza" value={form.data.starts_on} onChange={(event) => form.setData('starts_on', event.target.value)} error={form.errors.starts_on} />
                <TextField id="ends_on" type="date" label="Termina" value={form.data.ends_on} onChange={(event) => form.setData('ends_on', event.target.value)} error={form.errors.ends_on} />
            </div>
            <button type="submit" disabled={form.processing} className={buttonClass.primary}>
                Crear el cronograma
            </button>
        </form>
    );
}
