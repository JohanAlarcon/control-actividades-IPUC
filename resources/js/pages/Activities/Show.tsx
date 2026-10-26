import DecisionTrail from '@/components/activities/decision-trail';
import TaskList from '@/components/activities/task-list';
import InfoNote from '@/components/info-note';
import OriginBadge from '@/components/schedule/origin-badge';
import StatusBadge from '@/components/schedule/status-badge';
import MainLayout from '@/layouts/main-layout';
import { activityDate, money } from '@/lib/format';
import { isOwn, scheduleUrl } from '@/lib/schedule';
import { cardClass } from '@/lib/styles';
import type { CalendarActivity, OwnActivity, TaskItem, TrailEvent } from '@/types/schedule';
import { usePage } from '@inertiajs/react';
import { CalendarDays, Eye, HandCoins, MapPin, Shapes, UserRound } from 'lucide-react';
import type { ReactNode } from 'react';

type Detail = OwnActivity & { proposedBy: string | null; planYear: number | null; rejectionReason: string | null };

interface Props {
    activity: CalendarActivity | Detail;
    mine: boolean;
    tasks?: TaskItem[];
    canEdit?: boolean;
    canCheckTasks?: boolean;
    trail?: TrailEvent[];
}

/**
 * La ficha de una actividad (HU-09). La propia trae sus tareas y el rastro de decisiones; la de otro
 * comité, solo los siete datos que se comparten con toda la congregación (RF-010).
 */
export default function ActivityShow({ activity, mine, tasks = [], canEdit = false, canCheckTasks = false, trail }: Props) {
    const { errors } = usePage<{ errors: Record<string, string> }>().props;
    const own = isOwn(activity) ? (activity as Detail) : null;
    const year = new Date(activity.scheduledAt).getFullYear();

    return (
        <MainLayout title={activity.title} subtitle={activity.committee} back={{ href: scheduleUrl({ year }), label: `Cronograma ${year}` }}>
            {(errors.activity || errors.tasks) && (
                <p role="alert" className="mb-6 rounded-2xl bg-alerta/10 p-4 text-lg font-semibold text-alerta">
                    {errors.activity ?? errors.tasks}
                </p>
            )}

            <div className="lg:grid lg:grid-cols-2 lg:items-start lg:gap-6">
                <div>
                    <section className={`mb-6 p-4 ${cardClass}`}>
                        <div className="mb-3 flex flex-wrap gap-2">
                            <StatusBadge status={activity.status} />
                            {own && <OriginBadge fromPlan={own.fromPlan} />}
                        </div>
                        <ul className="flex flex-col gap-2">
                            <Fact icon={CalendarDays}>{activityDate(activity.scheduledAt)}</Fact>
                            {activity.location && <Fact icon={MapPin}>{activity.location}</Fact>}
                            {own?.type && <Fact icon={Shapes}>{own.type}</Fact>}
                            {own?.budget != null && <Fact icon={HandCoins}>Presupuesto estimado: {money(own.budget)}</Fact>}
                            {own?.proposedBy && <Fact icon={UserRound}>La propuso {own.proposedBy}</Fact>}
                        </ul>
                        {activity.description && <p className="mt-3 border-t border-ipuc/10 pt-3 text-lg leading-relaxed">{activity.description}</p>}
                        {own?.rejectionReason && <p className="mt-3 rounded-xl bg-alerta/10 p-3 text-lg text-alerta">Motivo: {own.rejectionReason}</p>}
                    </section>

                    {!mine && (
                        <InfoNote icon={Eye}>
                            Es una actividad de {activity.committee}. Aquí ve lo que se comparte con toda la congregación; su presupuesto y sus tareas son de
                            ese comité.
                        </InfoNote>
                    )}
                </div>

                {own && (
                    <div>
                        <TaskList activityId={own.id} tasks={tasks} progress={own.progress} canEdit={canEdit} canCheck={canCheckTasks} />
                        <DecisionTrail events={trail} />
                    </div>
                )}
            </div>
        </MainLayout>
    );
}

function Fact({ icon: Icon, children }: { icon: typeof CalendarDays; children: ReactNode }) {
    return (
        <li className="flex gap-3 text-lg">
            <Icon aria-hidden className="mt-1 size-5 shrink-0 text-ipuc-claro" />
            <span className="min-w-0 break-words">{children}</span>
        </li>
    );
}
