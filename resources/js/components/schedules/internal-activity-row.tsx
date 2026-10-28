import WhenFields from '@/components/activities/when-fields';
import { activityWhen, calendarDate, clockTime } from '@/lib/format';
import { buttonClass } from '@/lib/styles';
import type { InternalActivity } from '@/types/schedules';
import { router, useForm } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';

interface Props {
    activity: InternalActivity;
    /** El periodo del cronograma, si quien mira lo lleva y puede mover o cancelar esta fecha. */
    limits: { min: string; max: string } | null;
}

const linkClass = 'min-h-12 text-left text-lg font-semibold text-ipuc underline-offset-4 hover:underline';

/**
 * Una fecha del cronograma interno. Quien lo lleva la mueve o la cancela sola, y las demás fechas de
 * su serie siguen igual (CL-30). Cancelar pide confirmarlo y no borra: la fecha queda tachada.
 */
export default function InternalActivityRow({ activity, limits }: Props) {
    const [step, setStep] = useState<'closed' | 'open' | 'confirm'>('closed');
    const move = useForm({ date: calendarDate(new Date(activity.scheduledAt)), time: clockTime(activity.scheduledAt), ends: clockTime(activity.endsAt) });
    const faded = activity.cancelled ? 'text-tinta-suave line-through' : '';

    function submit(event: FormEvent) {
        event.preventDefault();
        move.post(`/activities/${activity.id}/move`, { preserveScroll: true, onSuccess: () => setStep('closed') });
    }

    function cancel() {
        router.post(`/activities/${activity.id}/cancel`, {}, { preserveScroll: true });
    }

    return (
        <li className="p-4 text-lg break-words">
            <p className={`font-semibold first-letter:uppercase ${faded || 'text-ipuc'}`}>{activityWhen(activity.scheduledAt, activity.endsAt)}</p>
            <p className={`font-bold ${faded}`}>{activity.title}</p>
            {activity.cancelled && <p className="font-semibold text-alerta">Cancelada</p>}
            {activity.theme && <p className="text-tema">«{activity.theme}»</p>}
            {(activity.type || activity.location) && <p className="text-tinta-suave">{[activity.type, activity.location].filter(Boolean).join(' · ')}</p>}
            {activity.responsible && <p className="text-tinta-suave">La dirige: {activity.responsible}</p>}

            {limits && !activity.cancelled && step === 'closed' && (
                <button type="button" onClick={() => setStep('open')} className={`mt-1 ${linkClass}`}>
                    Cambiar o cancelar esta fecha
                </button>
            )}

            {limits && step !== 'closed' && (
                <div className="mt-3 flex flex-col gap-4 rounded-xl bg-fondo p-3">
                    <form onSubmit={submit} noValidate className="flex flex-col gap-4">
                        <WhenFields idPrefix={`mover-${activity.id}-`} values={move.data} limits={limits} errors={move.errors} onChange={(field, value) => move.setData(field, value)} />
                        <button type="submit" disabled={move.processing} className={buttonClass.primary}>
                            Guardar la nueva fecha
                        </button>
                    </form>

                    {step === 'confirm' ? (
                        <div className="flex flex-col gap-2">
                            <p className="font-semibold">¿Cancelar solo esta fecha? Las demás siguen igual.</p>
                            <button type="button" onClick={cancel} className={buttonClass.danger}>
                                Sí, cancelarla
                            </button>
                            <button type="button" onClick={() => setStep('open')} className={buttonClass.secondary}>
                                No
                            </button>
                        </div>
                    ) : (
                        <button type="button" onClick={() => setStep('confirm')} className={buttonClass.secondary}>
                            Cancelar esta fecha
                        </button>
                    )}

                    <button type="button" onClick={() => setStep('closed')} className={linkClass}>
                        Dejarla como está
                    </button>
                </div>
            )}
        </li>
    );
}
