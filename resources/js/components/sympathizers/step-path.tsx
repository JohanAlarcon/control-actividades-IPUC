import TextField from '@/components/text-field';
import { calendarDate, shortDate } from '@/lib/format';
import { STAGES } from '@/lib/people';
import { buttonClass, cardClass } from '@/lib/styles';
import type { StageItem } from '@/types/people';
import { useForm } from '@inertiajs/react';
import { Check } from 'lucide-react';
import type { FormEvent } from 'react';

interface Props {
    sympathizerId: number;
    stages: StageItem[];
    current: number;
    canAdvance: boolean;
}

/**
 * El camino en cinco etapas, en orden y con la fecha de cada una (RF-042). La siguiente se anota
 * con su fecha; una etapa no se alcanza sin las anteriores.
 */
export default function StepPath({ sympathizerId, stages, current, canAdvance }: Props) {
    const form = useForm({ stage: current + 1, reached_at: calendarDate(new Date()) });

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post(`/sympathizers/${sympathizerId}/stages`, { preserveScroll: true });
    }

    return (
        <section aria-labelledby="etapas" className="mb-6">
            <h2 id="etapas" className="mb-3 px-1 text-xl font-bold">
                Su camino
            </h2>
            <ol className={`flex flex-col gap-4 p-4 ${cardClass}`}>
                {STAGES.map((label, index) => {
                    const stage = index + 1;
                    const reached = stages.find((item) => item.stage === stage);

                    return (
                        <li key={label} className="flex gap-3">
                            <span
                                aria-hidden
                                className={`flex size-10 shrink-0 items-center justify-center rounded-full text-lg font-bold ${reached ? 'bg-ipuc text-white' : 'bg-fondo text-tinta-suave ring-1 ring-linea'}`}
                            >
                                {reached ? <Check className="size-6" /> : stage}
                            </span>
                            <span className="min-w-0 flex-1">
                                <span className={`block text-lg ${reached ? 'font-bold' : 'text-tinta-suave'}`}>{label}</span>
                                {reached && <span className="block text-lg text-tinta-suave">Desde el {shortDate(`${reached.reachedAt}T12:00:00-05:00`)}</span>}

                                {stage === current + 1 && canAdvance && (
                                    <form onSubmit={submit} noValidate className="mt-3 flex flex-col gap-3">
                                        <TextField
                                            id="reached_at"
                                            type="date"
                                            label="¿Desde cuándo?"
                                            max={calendarDate(new Date())}
                                            value={form.data.reached_at}
                                            onChange={(event) => form.setData('reached_at', event.target.value)}
                                            error={form.errors.reached_at ?? form.errors.stage}
                                        />
                                        <button type="submit" disabled={form.processing} className={buttonClass.secondary}>
                                            Anotar esta etapa
                                        </button>
                                    </form>
                                )}
                            </span>
                        </li>
                    );
                })}
            </ol>
        </section>
    );
}
