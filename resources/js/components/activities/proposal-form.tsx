import ActivityTypePicker from '@/components/activities/activity-type-picker';
import TextField from '@/components/text-field';
import { dateLimits, type ActivityType } from '@/lib/activities';
import { buttonClass, errorClass, textAreaClass } from '@/lib/styles';
import { Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

type Proposal = {
    title: string;
    type: ActivityType | '';
    date: string;
    time: string;
    location: string;
    description: string;
    budget: string;
};

/**
 * Lo que se propone (HU-05, escenario 1). Obligatorios: el nombre, la clase, la fecha y la hora.
 * Si algo falta, el cursor salta al primer campo que hay que corregir (escenario 4).
 */
export default function ProposalForm() {
    const form = useForm<Proposal>({ title: '', type: '', date: '', time: '', location: '', description: '', budget: '' });
    const limits = dateLimits();

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post('/activities', {
            onError: (errors) => document.getElementById(Object.keys(errors)[0])?.focus(),
        });
    }

    return (
        <form onSubmit={submit} noValidate className="flex flex-col gap-5">
            <TextField
                id="title"
                label="Nombre de la actividad"
                hint="Por ejemplo: Vigilia de jóvenes."
                autoComplete="off"
                value={form.data.title}
                onChange={(event) => form.setData('title', event.target.value)}
                error={form.errors.title}
            />

            <ActivityTypePicker value={form.data.type} onChange={(type) => form.setData('type', type)} error={form.errors.type} />

            <div className="grid gap-5 sm:grid-cols-2">
                <TextField
                    id="date"
                    type="date"
                    label="Fecha"
                    min={limits.min}
                    max={limits.max}
                    value={form.data.date}
                    onChange={(event) => form.setData('date', event.target.value)}
                    error={form.errors.date}
                />
                <TextField id="time" type="time" label="Hora" value={form.data.time} onChange={(event) => form.setData('time', event.target.value)} error={form.errors.time} />
            </div>

            <TextField
                id="location"
                label="Lugar (si ya lo sabe)"
                hint="Por ejemplo: Templo central."
                value={form.data.location}
                onChange={(event) => form.setData('location', event.target.value)}
                error={form.errors.location}
            />

            <div>
                <label htmlFor="description" className="block text-lg font-semibold">
                    Descripción (opcional)
                </label>
                <textarea
                    id="description"
                    rows={4}
                    value={form.data.description}
                    onChange={(event) => form.setData('description', event.target.value)}
                    aria-invalid={form.errors.description ? true : undefined}
                    className={textAreaClass}
                />
                {form.errors.description && <p className={errorClass}>{form.errors.description}</p>}
            </div>

            <TextField
                id="budget"
                label="Presupuesto estimado (opcional)"
                hint="En pesos. Por ejemplo: 350.000."
                inputMode="numeric"
                value={form.data.budget}
                onChange={(event) => form.setData('budget', event.target.value)}
                error={form.errors.budget}
            />

            <div className="mt-2 flex flex-col gap-3">
                <button type="submit" disabled={form.processing} className={buttonClass.primary}>
                    Enviar la propuesta
                </button>
                <Link href="/approvals" className={buttonClass.secondary}>
                    Cancelar
                </Link>
            </div>
        </form>
    );
}
