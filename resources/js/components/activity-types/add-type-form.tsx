import TextField from '@/components/text-field';
import { buttonClass } from '@/lib/styles';
import { useForm } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import type { FormEvent } from 'react';

/** Agregar una clase de actividad. Desde ese momento se ofrece al proponer (D-25). */
export default function AddTypeForm() {
    const form = useForm({ name: '' });

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post('/activity-types', { preserveScroll: true, onSuccess: () => form.reset() });
    }

    return (
        <form onSubmit={submit} noValidate className="flex flex-col gap-4">
            <TextField
                id="new-type"
                label="Nombre de la clase"
                hint="Por ejemplo: Ayuno, o Vigilia."
                autoComplete="off"
                value={form.data.name}
                onChange={(event) => form.setData('name', event.target.value)}
                error={form.errors.name}
            />
            <button type="submit" disabled={form.processing} className={buttonClass.primary}>
                <Plus aria-hidden className="mr-2 size-6" />
                Agregar la clase
            </button>
        </form>
    );
}
