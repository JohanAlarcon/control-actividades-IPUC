import ApprovalActions from '@/components/approvals/approval-actions';
import ApprovalCard from '@/components/approvals/approval-card';
import InfoNote from '@/components/info-note';
import SectionBlock from '@/components/section-block';
import MainLayout from '@/layouts/main-layout';
import { decidesApprovals } from '@/lib/roles';
import type { ApprovalItem } from '@/types/approvals';
import type { SharedProps } from '@/types/shared';
import { usePage } from '@inertiajs/react';
import { CircleCheck, Info } from 'lucide-react';

interface Props {
    inbox: ApprovalItem[];
    escalated: ApprovalItem[];
    history: ApprovalItem[];
    counts: { inbox: number; escalated: number };
}

/**
 * La bandeja (T068). Quien decide ve primero lo que espera su decisión; los demás, en qué van las
 * solicitudes de su comité. Debajo, lo que está en manos de otra persona y lo ya resuelto.
 */
export default function ApprovalsIndex({ inbox, escalated, history, counts }: Props) {
    const { auth, scope, errors } = usePage<SharedProps & { errors: Record<string, string> }>().props;
    const decides = decidesApprovals(auth.user?.isPastor, scope.activeRole?.role);

    return (
        <MainLayout
            title={decides ? 'Aprobar solicitudes' : 'Solicitudes'}
            subtitle={decides ? 'Lo que espera su decisión y lo que ya resolvió.' : 'En qué van las solicitudes de su comité.'}
        >
            {errors.approval && (
                <p role="alert" className="mb-6 rounded-2xl bg-alerta/10 p-4 text-lg font-semibold text-alerta">
                    {errors.approval}
                </p>
            )}

            {decides && (
                <SectionBlock title={`Por resolver (${counts.inbox})`}>
                    <CardList items={inbox} empty="No tiene solicitudes por resolver. Todo está al día." withActions />
                </SectionBlock>
            )}

            {(!decides || counts.escalated > 0) && (
                <SectionBlock title={`Esperando respuesta (${counts.escalated})`}>
                    <CardList items={escalated} empty="No hay solicitudes esperando respuesta." showWhoDecides />
                </SectionBlock>
            )}

            <SectionBlock title="Ya resueltas">
                <CardList items={history} empty="Todavía no hay solicitudes resueltas." />
            </SectionBlock>
        </MainLayout>
    );
}

interface CardListProps {
    items: ApprovalItem[];
    empty: string;
    withActions?: boolean;
    showWhoDecides?: boolean;
}

function CardList({ items, empty, withActions = false, showWhoDecides = false }: CardListProps) {
    if (items.length === 0) {
        return <InfoNote icon={withActions ? CircleCheck : Info}>{empty}</InfoNote>;
    }

    return (
        <ul className="grid gap-4 lg:grid-cols-2 lg:items-start">
            {items.map((item) => (
                <li key={item.id}>
                    <ApprovalCard item={item} showWhoDecides={showWhoDecides}>
                        {withActions && <ApprovalActions item={item} />}
                    </ApprovalCard>
                </li>
            ))}
        </ul>
    );
}
