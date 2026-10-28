import TextField from '@/components/text-field';
import { buttonClass, cardClass, fieldClass } from '@/lib/styles';
import type { PrintCommittee, PrintPeriod } from '@/types/print';
import type { CommitteeOption } from '@/types/schedule';
import { router } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';

interface Props {
    committee: PrintCommittee;
    period: PrintPeriod;
    /** Llegan aparte, un momento después de la pantalla. */
    committees?: CommitteeOption[];
}

/**
 * El comité y las fechas de la hoja. Se aplican con el botón, no al escribir: así la hoja no cambia
 * mientras alguien todavía está escribiendo el año.
 */
export default function PrintFilters({ committee, period, committees }: Props) {
    const [choice, setChoice] = useState({ committee: committee.id, from: period.from, to: period.to });

    function submit(event: FormEvent) {
        event.preventDefault();
        router.get('/print/activities', choice, { only: ['committee', 'period', 'activities'], preserveState: true, preserveScroll: true, replace: true });
    }

    return (
        <form onSubmit={submit} className={`flex flex-col gap-4 p-4 ${cardClass}`}>
            {committees && committees.length > 1 && (
                <div>
                    <label htmlFor="imprimir-comite" className="block text-lg font-semibold">
                        Comité
                    </label>
                    <select
                        id="imprimir-comite"
                        value={choice.committee}
                        onChange={(event) => setChoice({ ...choice, committee: Number(event.target.value) })}
                        className={fieldClass}
                    >
                        {committees.map((option) => (
                            <option key={option.id} value={option.id}>
                                {'— '.repeat(option.depth - 1)}
                                {option.name}
                            </option>
                        ))}
                    </select>
                </div>
            )}
            <div className="grid gap-4 sm:grid-cols-2">
                <TextField id="imprimir-desde" label="Desde" type="date" value={choice.from} onChange={(event) => setChoice({ ...choice, from: event.target.value })} />
                <TextField id="imprimir-hasta" label="Hasta" type="date" min={choice.from} value={choice.to} onChange={(event) => setChoice({ ...choice, to: event.target.value })} />
            </div>
            <button type="submit" className={buttonClass.secondary}>
                Ver las actividades
            </button>
        </form>
    );
}
