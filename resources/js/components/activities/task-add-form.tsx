import TextField from '@/components/text-field';
import { buttonClass } from '@/lib/styles';
import { useForm } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import type { FormEvent } from 'react';

/** Agregar una tarea al plan de trabajo de la actividad: qué hay que hacer y, si se sabe, quién. */
export default function TaskAddForm({ activityId }: { activityId: number }) {
    const form = useForm({ description: '', assigned_to: '' });

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post(`/activities/${activityId}/tasks`, { preserveScroll: true, onSuccess: () => form.reset() });
    }

    return (
        <form onSubmit={submit} noValidate className="flex flex-col gap-4 border-t border-ipuc/10 p-4">
            <TextField
                id="nueva-tarea"
                label="Agregar una tarea"
                hint="Por ejemplo: Reservar el templo."
                autoComplete="off"
                value={form.data.description}
                onChange={(event) => form.setData('description', event.target.value)}
                error={form.errors.description}
            />
            <TextField
                id="a-cargo"
                label="¿Quién la hace? (opcional)"
                autoComplete="off"
                value={form.data.assigned_to}
                onChange={(event) => form.setData('assigned_to', event.target.value)}
                error={form.errors.assigned_to}
            />
            <button type="submit" disabled={form.processing} className={buttonClass.secondary}>
                <Plus aria-hidden className="mr-2 size-6" />
                Agregar la tarea
            </button>
        </form>
    );
}
