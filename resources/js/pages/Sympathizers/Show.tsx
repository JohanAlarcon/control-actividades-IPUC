import CommitmentCard from '@/components/sympathizers/commitment-card';
import StepPath from '@/components/sympathizers/step-path';
import VisitLog from '@/components/sympathizers/visit-log';
import MainLayout from '@/layouts/main-layout';
import { shortDate } from '@/lib/format';
import { ageText, ARRIVAL_SOURCES, STAGES } from '@/lib/people';
import { buttonClass, cardClass } from '@/lib/styles';
import type { StageItem, SympathizerDetail, Thresholds, VisitItem } from '@/types/people';
import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';

interface Props {
    sympathizer: SympathizerDetail;
    stages: StageItem[];
    thresholds: Thresholds;
    canPromote: boolean;
    visits?: VisitItem[];
}

/** La ficha de seguimiento (HU-14): sus datos, su compromiso, su camino y sus visitas. */
export default function SympathizerShow({ sympathizer, stages, thresholds, canPromote, visits }: Props) {
    const { errors } = usePage<{ errors: Record<string, string> }>().props;
    const [confirming, setConfirming] = useState(false);
    const arrival = ARRIVAL_SOURCES.find((item) => item.value === sympathizer.arrivalSource)?.label;
    const facts = [
        ageText(sympathizer.age),
        sympathizer.phone,
        sympathizer.neighborhood,
        arrival && `Llegó: ${arrival.toLowerCase()}`,
        sympathizer.invitedBy && `Lo registró o lo invitó: ${sympathizer.invitedBy}`,
        `Primer contacto: ${shortDate(`${sympathizer.firstContactAt}T12:00:00-05:00`)}`,
        sympathizer.dataConsent ? 'Autorizó el manejo de sus datos' : 'Sin autorización de datos anotada',
    ].filter(Boolean);

    return (
        <MainLayout
            title={sympathizer.name}
            subtitle={`Etapa ${sympathizer.stage} de 5 · ${STAGES[sympathizer.stage - 1]}`}
            back={{ href: '/sympathizers', label: 'Simpatizantes' }}
        >
            {errors.promote && (
                <p role="alert" className="mb-6 rounded-2xl bg-alerta/10 p-4 text-lg font-semibold text-alerta">
                    {errors.promote}
                </p>
            )}

            <div className="lg:grid lg:grid-cols-2 lg:items-start lg:gap-6">
                <div>
                    <ul className={`mb-6 flex flex-col gap-1 p-4 text-lg ${cardClass}`}>
                        {facts.map((fact) => (
                            <li key={fact as string}>{fact}</li>
                        ))}
                    </ul>
                    <CommitmentCard commitment={sympathizer.commitment} thresholds={thresholds} />
                    <StepPath sympathizerId={sympathizer.id} stages={stages} current={sympathizer.stage} canAdvance={sympathizer.stage < 5 && !sympathizer.convertedAt} />

                    {sympathizer.promotionPending && <p className={`mb-6 p-4 text-lg font-semibold text-ipuc ${cardClass}`}>El paso a miembro está esperando al pastor.</p>}
                    {canPromote && (
                        <section className={`mb-6 p-4 ${cardClass}`}>
                            <p className="text-lg font-bold">Recorrió todo el camino</p>
                            <p className="text-lg text-tinta-suave">Puede proponer que pase a ser miembro. La solicitud le llega al pastor, que es quien aprueba.</p>
                            {confirming ? (
                                <div className="mt-3 grid grid-cols-2 gap-3">
                                    <button type="button" onClick={() => setConfirming(false)} className={buttonClass.secondary}>
                                        Volver
                                    </button>
                                    <button type="button" onClick={() => router.post(`/sympathizers/${sympathizer.id}/promote`, {}, { preserveScroll: true })} className={buttonClass.primary}>
                                        Sí, proponerlo
                                    </button>
                                </div>
                            ) : (
                                <button type="button" onClick={() => setConfirming(true)} className={`${buttonClass.primary} mt-3`}>
                                    Proponer que pase a ser miembro
                                </button>
                            )}
                        </section>
                    )}
                </div>

                <VisitLog sympathizerId={sympathizer.id} visits={visits} />
            </div>
        </MainLayout>
    );
}
