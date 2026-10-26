import { activityDate, money, shortDate, waitingSince } from '@/lib/format';
import { cardClass } from '@/lib/styles';
import type { ApprovalItem, ApprovalType } from '@/types/approvals';
import { CalendarClock, CalendarDays, ClipboardList, Clock, HandCoins, Hourglass, UserCheck, UserPlus, UserRound, type LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';

const TYPES: Record<ApprovalType, { label: string; icon: LucideIcon }> = {
    activity: { label: 'Actividad nueva', icon: CalendarDays },
    expense: { label: 'Gasto', icon: HandCoins },
    task: { label: 'Tarea', icon: ClipboardList },
    date_change: { label: 'Cambio de fecha', icon: CalendarClock },
    new_member: { label: 'Miembro nuevo', icon: UserPlus },
};

interface Props {
    item: ApprovalItem;
    /** En la lista de lo que espera a otra persona: dice en manos de quién está. */
    showWhoDecides?: boolean;
    children?: ReactNode;
}

/**
 * Una solicitud, completa a 360 px sin abrir nada (T068): qué es, de qué comité viene, quién la
 * pidió, por quién ya pasó, cuánto cuesta, qué mueve en el cronograma y desde cuándo espera.
 */
export default function ApprovalCard({ item, showWhoDecides = false, children }: Props) {
    const { label, icon: Icon } = TYPES[item.type];

    return (
        <article className={`${cardClass} p-4`}>
            <header className="flex gap-3">
                <span aria-hidden className="flex size-12 shrink-0 items-center justify-center rounded-xl bg-ipuc/10 text-ipuc">
                    <Icon className="size-6" />
                </span>
                <div className="min-w-0 flex-1">
                    <p className="text-lg font-semibold text-ipuc">{label}</p>
                    <h3 className="text-xl leading-snug font-bold break-words">{item.title}</h3>
                    <p className="text-lg text-tinta-suave">{item.committee}</p>
                </div>
            </header>

            <ul className="mt-3 flex flex-col gap-2 border-t border-ipuc/10 pt-3">
                <Fact icon={UserRound}>La pidió {item.requestedBy}</Fact>
                {item.escalatedBy && <Fact icon={UserCheck}>Ya la aprobó {item.escalatedBy}</Fact>}
                {item.amount !== null && (
                    <Fact icon={HandCoins}>
                        Monto: <strong>{money(item.amount)}</strong>
                    </Fact>
                )}
                {item.activity && (
                    <Fact icon={CalendarDays}>
                        {activityDate(item.activity.date)}
                        {item.activity.location && ` · ${item.activity.location}`}
                    </Fact>
                )}
                {item.detail && <Fact icon={ClipboardList}>{item.detail}</Fact>}
                {showWhoDecides && item.status === 'pending' && (
                    <Fact icon={Hourglass}>Está en manos {item.level === 'pastor' ? 'del pastor' : 'de la presidencia del comité'}</Fact>
                )}
            </ul>

            <Outcome item={item} />
            {children}
        </article>
    );
}

function Fact({ icon: Icon, children }: { icon: LucideIcon; children: ReactNode }) {
    return (
        <li className="flex gap-3 text-lg">
            <Icon aria-hidden className="mt-1 size-5 shrink-0 text-ipuc-claro" />
            <span className="min-w-0 break-words">{children}</span>
        </li>
    );
}

/** Lo pendiente dice desde cuándo espera; lo resuelto, qué se decidió, quién y cuándo, y el motivo si no se aprobó. */
function Outcome({ item }: { item: ApprovalItem }) {
    if (item.status === 'pending') {
        return (
            <p className="mt-3 inline-flex items-center gap-2 rounded-full bg-ipuc-dorado/25 px-3 py-1 text-lg font-semibold">
                <Clock aria-hidden className="size-5" />
                Espera {waitingSince(item.waitingSince)}
            </p>
        );
    }

    const approved = item.status === 'approved';

    return (
        <div className={`mt-3 rounded-xl p-3 text-lg ${approved ? 'bg-ipuc/10 text-ipuc' : 'bg-alerta/10 text-alerta'}`}>
            <p className="font-bold">
                {approved ? 'Aprobada' : 'No aprobada'}
                {item.resolvedBy && ` por ${item.resolvedBy}`}
                {item.resolvedAt && `, el ${shortDate(item.resolvedAt)}`}
            </p>
            {item.reason && <p className="mt-1 text-tinta">Motivo: {item.reason}</p>}
        </div>
    );
}
