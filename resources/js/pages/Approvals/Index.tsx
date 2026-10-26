import ApprovalActions from '@/components/approvals/approval-actions';
import ApprovalCard from '@/components/approvals/approval-card';
import BulkApprove from '@/components/approvals/bulk-approve';
import InfoNote from '@/components/info-note';
import SectionBlock from '@/components/section-block';
import MainLayout from '@/layouts/main-layout';
import { canPropose, decidesApprovals } from '@/lib/roles';
import { buttonClass } from '@/lib/styles';
import type { ApprovalItem } from '@/types/approvals';
import type { SharedProps } from '@/types/shared';
import { Link, usePage } from '@inertiajs/react';
import { CalendarPlus, CircleCheck, Info } from 'lucide-react';

interface Props {
    inbox: ApprovalItem[];
    escalated: ApprovalItem[];
    history: ApprovalItem[];
    counts: { inbox: number; escalated: number };
}

/** Lo que dice la bandeja según quién la abre: el pastor aprueba; el presidente revisa (D-24). */
const TEXTS = {
    pastor: {
        title: 'Aprobar solicitudes',
        subtitle: 'Usted es quien aprueba. Esto es lo que espera su decisión.',
        inbox: 'Por aprobar',
        empty: 'No tiene solicitudes por aprobar. Todo está al día.',
    },
    president: {
        title: 'Revisar solicitudes',
        subtitle: 'Usted les da el visto bueno o las devuelve. El pastor es quien aprueba.',
        inbox: 'Por revisar',
        empty: 'No tiene solicitudes por revisar. Todo está al día.',
    },
    others: { title: 'Solicitudes', subtitle: 'En qué van las solicitudes de su comité.', inbox: '', empty: '' },
};

/**
 * La bandeja (T068). Quien decide ve primero lo que espera su decisión; los demás, en qué van las
 * solicitudes de su comité. Debajo, lo que está en manos de otra persona y lo ya resuelto.
 */
export default function ApprovalsIndex({ inbox, escalated, history, counts }: Props) {
    const { auth, scope, errors } = usePage<SharedProps & { errors: Record<string, string> }>().props;
    const decides = decidesApprovals(auth.user?.isPastor, scope.activeRole?.role);
    const texts = TEXTS[auth.user?.isPastor ? 'pastor' : decides ? 'president' : 'others'];

    return (
        <MainLayout title={texts.title} subtitle={texts.subtitle}>
            {errors.approval && (
                <p role="alert" className="mb-6 rounded-2xl bg-alerta/10 p-4 text-lg font-semibold text-alerta">
                    {errors.approval}
                </p>
            )}

            {canPropose(scope.activeRole?.role) && (
                <Link href="/activities/create" className={`${buttonClass.primary} mb-6 lg:w-fit lg:px-6`}>
                    <CalendarPlus aria-hidden className="mr-2 size-6" />
                    Proponer una actividad
                </Link>
            )}

            {decides && (
                <SectionBlock title={`${texts.inbox} (${counts.inbox})`}>
                    {auth.user?.isPastor && <BulkApprove items={inbox} />}
                    <CardList items={inbox} empty={texts.empty} withActions />
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
