import ActivityTypePicker from '@/components/activities/activity-type-picker';
import ConflictWarning from '@/components/activities/conflict-warning';
import MoneyField from '@/components/money-field';
import TextField from '@/components/text-field';
import { dateLimits } from '@/lib/activities';
import { scheduleUrl } from '@/lib/schedule';
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
 * Lo que se propone (HU-05, escenario 1), o lo que se carga al plan del año (HU-08). Obligatorios:
 * el nombre, la clase, la fecha y la hora. Si algo falta, el cursor salta al primer campo que hay
 * que corregir (escenario 4). Si ese día ya hay otra actividad, avisa antes de guardar (escenario 5).
 */
export default function ProposalForm({ types, plan }: { types: ActivityTypeOption[]; plan: number | null }) {
    const form = useForm<Proposal>({ title: '', activity_type_id: '', date: '', time: '', location: '', description: '', budget: '' });
    const errors = form.errors as Partial<Record<keyof Proposal | 'conflict' | 'plan', string>>;
    const limits = dateLimits(new Date(), plan ?? undefined);

    function send(insist: boolean) {
        form.transform((data) => ({ ...data, confirm_conflict: insist }));
        form.post(plan ? `/annual-plan/${plan}/activities` : '/activities', {
            onError: (found) => {
                if (!('conflict' in found)) document.getElementById(Object.keys(found)[0])?.focus();
            },
        });
    }

    function submit(event: FormEvent) {
        event.preventDefault();
        send(false);
    }

    /** Lo que mueve el día, la hora o el lugar deja sin efecto el aviso de cruce. */
    function setWhen(field: 'date' | 'time' | 'location', value: string) {
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

            <ActivityTypePicker
                types={types}
                value={form.data.activity_type_id}
                onChange={(id) => form.setData('activity_type_id', id)}
                error={errors.activity_type_id}
            />

            <div className="grid gap-5 sm:grid-cols-2">
                <TextField
                    id="date"
                    type="date"
                    label="Fecha"
                    min={limits.min}
                    max={limits.max}
                    value={form.data.date}
                    onChange={(event) => setWhen('date', event.target.value)}
                    error={errors.date}
                />
                <TextField id="time" type="time" label="Hora" value={form.data.time} onChange={(event) => setWhen('time', event.target.value)} error={errors.time} />
            </div>

            <TextField
                id="location"
                label="Lugar (si ya lo sabe)"
                hint="Por ejemplo: Templo central."
                value={form.data.location}
                onChange={(event) => setWhen('location', event.target.value)}
                error={errors.location}
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
                    aria-invalid={errors.description ? true : undefined}
                    className={textAreaClass}
                />
                {errors.description && <p className={errorClass}>{errors.description}</p>}
            </div>

            <MoneyField
                id="budget"
                label="Presupuesto estimado (opcional)"
                hint="En pesos, sin centavos."
                value={form.data.budget}
                onChange={(budget) => form.setData('budget', budget)}
                error={errors.budget}
            />

            {errors.plan && <p className={errorClass}>{errors.plan}</p>}

            {errors.conflict ? (
                <ConflictWarning
                    message={errors.conflict}
                    busy={form.processing}
                    onInsist={() => send(true)}
                    onChangeDate={() => {
                        form.clearErrors('conflict' as keyof Proposal);
                        document.getElementById('date')?.focus();
                    }}
                />
            ) : (
                <div className="mt-2 flex flex-col gap-3">
                    <button type="submit" disabled={form.processing} className={buttonClass.primary}>
                        {plan ? 'Cargarla al plan' : 'Enviar la propuesta'}
                    </button>
                    <Link href={plan ? scheduleUrl({ year: plan }) : '/approvals'} className={buttonClass.secondary}>
                        Cancelar
                    </Link>
                </div>
            )}
        </form>
    );
}
