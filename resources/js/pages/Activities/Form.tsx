import ProposalForm from '@/components/activities/proposal-form';
import InfoNote from '@/components/info-note';
import MainLayout from '@/layouts/main-layout';
import { cardClass } from '@/lib/styles';
import type { SharedProps } from '@/types/shared';
import { usePage } from '@inertiajs/react';
import { Send } from 'lucide-react';

/** Proponer una actividad que no estaba en el plan (HU-05). Desde antes de enviarla se sabe a quién le llega. */
export default function ActivityForm({ recipient }: { recipient: string | null }) {
    const { scope } = usePage<SharedProps>().props;

    return (
        <MainLayout title="Proponer una actividad" subtitle={scope.activeRole?.committeeName ?? undefined} back={{ href: '/approvals', label: 'Solicitudes' }}>
            <div className="lg:max-w-2xl">
                <div className="mb-5">
                    <InfoNote icon={Send}>
                        {recipient ? (
                            <>
                                Su propuesta le llegará a <strong>{recipient}</strong>, que es quien la revisa.
                            </>
                        ) : (
                            'Su comité todavía no tiene quién revise las propuestas. Puede enviarla igual: le llegará a la persona que el pastor nombre.'
                        )}
                    </InfoNote>
                </div>

                <div className={`p-4 ${cardClass}`}>
                    <ProposalForm />
                </div>
            </div>
        </MainLayout>
    );
}
