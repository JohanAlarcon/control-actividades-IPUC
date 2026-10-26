import ProposalForm from '@/components/activities/proposal-form';
import InfoNote from '@/components/info-note';
import MainLayout from '@/layouts/main-layout';
import { scheduleUrl } from '@/lib/schedule';
import { cardClass } from '@/lib/styles';
import type { ActivityTypeOption } from '@/types/activities';
import type { SharedProps } from '@/types/shared';
import { usePage } from '@inertiajs/react';
import { CalendarRange, Send } from 'lucide-react';

interface Props {
    recipient: string | null;
    types: ActivityTypeOption[];
    /** El año del plan al que se carga la actividad; nulo cuando es una propuesta aparte. */
    plan: number | null;
}

/**
 * Proponer una actividad que no estaba en el plan (HU-05), o cargarla al plan del año (HU-08).
 * Desde antes de enviarla se sabe a quién le llega, o que la aprueba el pastor con el plan completo.
 */
export default function ActivityForm({ recipient, types, plan }: Props) {
    const { scope } = usePage<SharedProps>().props;

    return (
        <MainLayout
            title={plan ? `Cargar al plan ${plan}` : 'Proponer una actividad'}
            subtitle={scope.activeRole?.committeeName ?? undefined}
            back={plan ? { href: scheduleUrl({ year: plan }), label: `Cronograma ${plan}` } : { href: '/approvals', label: 'Solicitudes' }}
        >
            <div className="lg:max-w-2xl">
                <div className="mb-5">
                    {plan ? (
                        <InfoNote icon={CalendarRange}>
                            Va al plan {plan}. El pastor lo aprueba completo, de una sola vez: no hace falta pedir aprobación por cada actividad.
                        </InfoNote>
                    ) : (
                        <InfoNote icon={Send}>
                            {recipient ? (
                                scope.activeRole?.role === 'president' ? (
                                    <>
                                        Su propuesta le llegará a <strong>{recipient}</strong>, que es quien aprueba.
                                    </>
                                ) : (
                                    <>
                                        Primero la revisa <strong>{recipient}</strong>. Si le da el visto bueno, pasa al pastor, que es quien aprueba.
                                    </>
                                )
                            ) : (
                                'Su comité todavía no tiene quién revise las propuestas. Puede enviarla igual: le llegará a la persona que el pastor nombre.'
                            )}
                        </InfoNote>
                    )}
                </div>

                <div className={`p-4 ${cardClass}`}>
                    <ProposalForm types={types} plan={plan} />
                </div>
            </div>
        </MainLayout>
    );
}
