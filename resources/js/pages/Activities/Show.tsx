import DecisionTrail from '@/components/activities/decision-trail';
import TaskList from '@/components/activities/task-list';
import HighlightBadge from '@/components/schedule/highlight-badge';
import OriginBadge from '@/components/schedule/origin-badge';
import StatusBadge from '@/components/schedule/status-badge';
import MainLayout from '@/layouts/main-layout';
import { activityWhen, money } from '@/lib/format';
import { scheduleUrl } from '@/lib/schedule';
import { cardClass } from '@/lib/styles';
import type { OwnActivity, TaskItem, TrailEvent } from '@/types/schedule';
import { usePage } from '@inertiajs/react';
import { CalendarDays, HandCoins, MapPin, Shapes, UserRound } from 'lucide-react';
import type { ReactNode } from 'react';

type Detail = OwnActivity & { proposedBy: string | null; planYear: number | null; rejectionReason: string | null };

interface Props {
    activity: Detail;
    tasks?: TaskItem[];
    canEdit?: boolean;
    canCheckTasks?: boolean;
    trail?: TrailEvent[];
}

/**
 * La ficha de una actividad (HU-09), con sus tareas y el rastro de decisiones. Solo la abren su
 * comité, sus ascendientes y el pastor: de lo ajeno, el calendario muestra lo que se comparte (D-36).
 */
export default function ActivityShow({ activity, tasks = [], canEdit = false, canCheckTasks = false, trail }: Props) {
    const { errors } = usePage<{ errors: Record<string, string> }>().props;
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
                            <OriginBadge fromPlan={activity.fromPlan} />
                            {activity.highlight && <HighlightBadge highlight={activity.highlight} />}
                        </div>
                        {activity.theme && <p className="mb-3 text-lg font-semibold break-words text-tema">{activity.theme}</p>}
                        <ul className="flex flex-col gap-2">
                            <Fact icon={CalendarDays}>{activityWhen(activity.scheduledAt, activity.endsAt)}</Fact>
                            {activity.location && <Fact icon={MapPin}>{activity.location}</Fact>}
                            {activity.type && <Fact icon={Shapes}>{activity.type}</Fact>}
                            {activity.budget != null && <Fact icon={HandCoins}>Presupuesto estimado: {money(activity.budget)}</Fact>}
                            {activity.proposedBy && <Fact icon={UserRound}>La propuso {activity.proposedBy}</Fact>}
                        </ul>
                        {activity.description && <p className="mt-3 border-t border-ipuc/10 pt-3 text-lg leading-relaxed">{activity.description}</p>}
                        {activity.rejectionReason && <p className="mt-3 rounded-xl bg-alerta/10 p-3 text-lg text-alerta">Motivo: {activity.rejectionReason}</p>}
                    </section>
                </div>

                <div>
                    <TaskList activityId={activity.id} tasks={tasks} progress={activity.progress} canEdit={canEdit} canCheck={canCheckTasks} />
                    <DecisionTrail events={trail} />
                </div>
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
