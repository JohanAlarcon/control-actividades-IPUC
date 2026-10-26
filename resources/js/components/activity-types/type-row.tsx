import TextField from '@/components/text-field';
import { usesText } from '@/lib/activities';
import { buttonClass } from '@/lib/styles';
import type { ManagedActivityType } from '@/types/activities';
import { router, useForm } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';

/** Una clase de actividad: cuánto se ha usado, y sus dos acciones. Nunca se borra (D-25). */
export default function TypeRow({ type }: { type: ManagedActivityType }) {
    const [renaming, setRenaming] = useState(false);
    const [busy, setBusy] = useState(false);
    const form = useForm({ name: type.name });

    function rename(event: FormEvent) {
        event.preventDefault();
        form.patch(`/activity-types/${type.id}`, { preserveScroll: true, onSuccess: () => setRenaming(false) });
    }

    function cancel() {
        setRenaming(false);
        form.reset();
        form.clearErrors();
    }

    function toggle() {
        router.patch(
            `/activity-types/${type.id}`,
            { active: !type.active },
            { preserveScroll: true, onStart: () => setBusy(true), onFinish: () => setBusy(false) },
        );
    }

    if (renaming) {
        return (
            <li className="p-4">
                <form onSubmit={rename} noValidate className="flex flex-col gap-3">
                    <TextField
                        id={`clase-${type.id}`}
                        label="Nuevo nombre"
                        autoFocus
                        autoComplete="off"
                        value={form.data.name}
                        onChange={(event) => form.setData('name', event.target.value)}
                        error={form.errors.name}
                    />
                    <div className="grid grid-cols-2 gap-3">
                        <button type="button" onClick={cancel} className={buttonClass.secondary}>
                            Volver
                        </button>
                        <button type="submit" disabled={form.processing} className={buttonClass.primary}>
                            Guardar
                        </button>
                    </div>
                </form>
            </li>
        );
    }

    return (
        <li className="flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
            <div className="min-w-0 flex-1">
                <p className={`text-lg font-bold ${type.active ? 'text-tinta' : 'text-tinta-suave'}`}>{type.name}</p>
                <p className="text-lg text-tinta-suave">{usesText(type.uses)}</p>
            </div>
            <div className="flex flex-col gap-2 sm:flex-row">
                <button type="button" onClick={() => setRenaming(true)} className={`${buttonClass.secondary} sm:w-auto`}>
                    Cambiar el nombre
                </button>
                <button type="button" onClick={toggle} disabled={busy} className={`${buttonClass.secondary} sm:w-auto`}>
                    {type.active ? 'Desactivar' : 'Activar'}
                </button>
            </div>
        </li>
    );
}
