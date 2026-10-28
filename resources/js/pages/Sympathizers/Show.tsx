import AssignmentPanel, { type AssignmentItem } from '@/components/sympathizers/assignment-panel';
import type { CommitteeOption } from '@/components/sympathizers/committee-choice';
import CommitmentCard from '@/components/sympathizers/commitment-card';
import PromotePanel from '@/components/sympathizers/promote-panel';
import StepPath from '@/components/sympathizers/step-path';
import VisitLog from '@/components/sympathizers/visit-log';
import MainLayout from '@/layouts/main-layout';
import { shortDate } from '@/lib/format';
import { ageText, ARRIVAL_SOURCES, STAGES } from '@/lib/people';
import { cardClass } from '@/lib/styles';
import type { StageItem, SympathizerDetail, Thresholds, VisitItem } from '@/types/people';
import { usePage } from '@inertiajs/react';

interface Props {
    sympathizer: SympathizerDetail;
    stages: StageItem[];
    thresholds: Thresholds;
    canPromote: boolean;
    canAssign: boolean;
    committees?: CommitteeOption[] | null;
    assignments?: AssignmentItem[];
    visits?: VisitItem[];
}

/**
 * La ficha de seguimiento (HU-14): sus datos, qué comité la acompaña y por cuáles pasó, su
 * compromiso, su camino y sus visitas.
 */
export default function SympathizerShow({ sympathizer, stages, thresholds, canPromote, canAssign, committees, assignments, visits }: Props) {
    const { errors } = usePage<{ errors: Record<string, string> }>().props;
    const arrival = ARRIVAL_SOURCES.find((item) => item.value === sympathizer.arrivalSource)?.label;
    const facts = [
        ageText(sympathizer.age),
        sympathizer.phone,
        sympathizer.neighborhood,
        arrival && `Llegó: ${arrival.toLowerCase()}`,
        sympathizer.invitedBy && `La registró o la invitó: ${sympathizer.invitedBy}`,
        `Primer contacto: ${shortDate(`${sympathizer.firstContactAt}T12:00:00-05:00`)}`,
        sympathizer.dataConsent ? 'Autorizó el manejo de sus datos' : 'Sin autorización de datos anotada',
    ].filter(Boolean);

    return (
        <MainLayout title={sympathizer.name} subtitle={`Etapa ${sympathizer.stage} de 5 · ${STAGES[sympathizer.stage - 1]}`} back={{ href: '/sympathizers', label: 'Simpatizantes' }}>
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
                    <AssignmentPanel sympathizerId={sympathizer.id} committee={sympathizer.committee} canAssign={canAssign} committees={committees} assignments={assignments} />
                    <CommitmentCard commitment={sympathizer.commitment} thresholds={thresholds} />
                    <StepPath sympathizerId={sympathizer.id} stages={stages} current={sympathizer.stage} canAdvance={sympathizer.stage < 5 && !sympathizer.convertedAt} />

                    {sympathizer.promotionPending && <p className={`mb-6 p-4 text-lg font-semibold text-ipuc ${cardClass}`}>El paso a miembro está esperando al pastor.</p>}
                    {canPromote && <PromotePanel sympathizerId={sympathizer.id} />}
                </div>

                <VisitLog sympathizerId={sympathizer.id} visits={visits} />
            </div>
        </MainLayout>
    );
}
