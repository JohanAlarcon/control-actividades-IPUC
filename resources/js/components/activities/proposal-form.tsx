import ActivityTypePicker from '@/components/activities/activity-type-picker';
import ConflictWarning from '@/components/activities/conflict-warning';
import HighlightFields from '@/components/activities/highlight-fields';
import WhenFields, { type WhenField } from '@/components/activities/when-fields';
import MoneyField from '@/components/money-field';
import TextAreaField from '@/components/text-area-field';
import TextField from '@/components/text-field';
import { dateLimits } from '@/lib/activities';
import { scheduleUrl } from '@/lib/schedule';
import { buttonClass, errorClass } from '@/lib/styles';
import type { ActivityTypeOption } from '@/types/activities';
import type { Highlight } from '@/types/schedule';
import { Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

type Proposal = Record<WhenField, string> & {
    title: string;
    theme: string;
    highlight: Highlight | '';
    activity_type_id: number | '';
    location: string;
    description: string;
    budget: string;
};

/**
 * Lo que se propone (HU-05, escenario 1), o lo que se carga al plan del año (HU-08). Obligatorios:
 * el nombre, la clase, la fecha y las horas de inicio y de fin. Si algo falta, el cursor salta al
 * primer campo que hay que corregir (escenario 4). Si el horario se cruza con otra actividad del
 * cronograma general, no se guarda y se dice con cuál (escenario 5, D-37).
 */
export default function ProposalForm({ types, plan }: { types: ActivityTypeOption[]; plan: number | null }) {
    const form = useForm<Proposal>({ title: '', theme: '', highlight: '', activity_type_id: '', date: '', time: '', ends: '', location: '', description: '', budget: '' });
    const errors = form.errors as Partial<Record<keyof Proposal | 'conflict' | 'plan', string>>;
    const limits = dateLimits(new Date(), plan ?? undefined);

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post(plan ? `/annual-plan/${plan}/activities` : '/activities', {
            onError: (found) => {
                if (!('conflict' in found)) document.getElementById(Object.keys(found)[0])?.focus();
            },
        });
    }

    /** Lo que mueve el día o la hora deja sin efecto el aviso de cruce. */
    function setWhen(field: WhenField, value: string) {
        form.setData(field, value);
        form.clearErrors('conflict' as keyof Proposal);
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
                error={errors.title}
            />
            <ActivityTypePicker types={types} value={form.data.activity_type_id} onChange={(id) => form.setData('activity_type_id', id)} error={errors.activity_type_id} />
            <WhenFields values={form.data} limits={limits} errors={errors} onChange={setWhen} />
            <TextField
                id="location"
                label="Lugar (si ya lo sabe)"
                hint="Por ejemplo: Templo central."
                value={form.data.location}
                onChange={(event) => form.setData('location', event.target.value)}
                error={errors.location}
            />
            <HighlightFields
                theme={form.data.theme}
                highlight={form.data.highlight}
                errors={errors}
                onTheme={(theme) => form.setData('theme', theme)}
                onHighlight={(highlight) => form.setData('highlight', highlight)}
            />
            <TextAreaField id="description" label="Descripción (opcional)" value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} error={errors.description} />
            <MoneyField
                id="budget"
                label="Presupuesto estimado (opcional)"
                hint="En pesos, sin centavos."
                value={form.data.budget}
                onChange={(budget) => form.setData('budget', budget)}
                error={errors.budget}
            />

            {errors.plan && <p className={errorClass}>{errors.plan}</p>}
            {errors.conflict && <ConflictWarning message={errors.conflict} onChangeTime={() => document.getElementById('time')?.focus()} />}

            <div className="mt-2 flex flex-col gap-3">
                <button type="submit" disabled={form.processing} className={buttonClass.primary}>
                    {plan ? 'Cargarla al plan' : 'Enviar la propuesta'}
                </button>
                <Link href={plan ? scheduleUrl({ year: plan }) : '/approvals'} className={buttonClass.secondary}>
                    Cancelar
                </Link>
            </div>
        </form>
    );
}
