import ActivityTypePicker from '@/components/activities/activity-type-picker';
import MoneyField from '@/components/money-field';
import TextField from '@/components/text-field';
import { dateLimits } from '@/lib/activities';
import { buttonClass, errorClass, textAreaClass } from '@/lib/styles';
import type { ActivityTypeOption } from '@/types/activities';
import { Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

type Proposal = {
    title: string;
    activity_type_id: number | '';
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
export default function ProposalForm({ types }: { types: ActivityTypeOption[] }) {
    const form = useForm<Proposal>({ title: '', activity_type_id: '', date: '', time: '', location: '', description: '', budget: '' });
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

            <ActivityTypePicker
                types={types}
                value={form.data.activity_type_id}
                onChange={(id) => form.setData('activity_type_id', id)}
                error={form.errors.activity_type_id}
            />

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

            <MoneyField
                id="budget"
                label="Presupuesto estimado (opcional)"
                hint="En pesos, sin centavos."
                value={form.data.budget}
                onChange={(budget) => form.setData('budget', budget)}
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
