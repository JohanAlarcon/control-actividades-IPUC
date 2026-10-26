import TaskAddForm from '@/components/activities/task-add-form';
import { buttonClass, cardClass } from '@/lib/styles';
import type { TaskItem } from '@/types/schedule';
import { router } from '@inertiajs/react';
import { Check, CircleCheckBig } from 'lucide-react';
import { useState } from 'react';

interface Props {
    activityId: number;
    tasks: TaskItem[];
    progress: number;
    canEdit: boolean;
    canCheck: boolean;
}

/**
 * Las tareas de la actividad. Al marcar una, el avance y el estado se recalculan solos (HU-09,
 * escenario 4). Sin tareas, el responsable puede darla por cumplida (R-04).
 */
export default function TaskList({ activityId, tasks, progress, canEdit, canCheck }: Props) {
    const [busy, setBusy] = useState(false);
    const options = { preserveScroll: true, onStart: () => setBusy(true), onFinish: () => setBusy(false) };

    return (
        <section aria-labelledby="tareas" className="mb-6">
            <h2 id="tareas" className="mb-3 px-1 text-xl font-bold">
                Tareas {tasks.length > 0 && <span className="font-normal text-tinta-suave">· avance {progress}%</span>}
            </h2>

            <div className={`overflow-hidden ${cardClass}`}>
                {tasks.length > 0 && (
                    <ul className="divide-y divide-ipuc/5">
                        {tasks.map((task) => (
                            <li key={task.id} className="flex items-center gap-3 p-4">
                                <button
                                    type="button"
                                    disabled={!canCheck || busy}
                                    aria-pressed={task.done}
                                    aria-label={task.done ? `Desmarcar «${task.description}»` : `Marcar «${task.description}» como cumplida`}
                                    onClick={() => router.post(`/activities/${activityId}/tasks/${task.id}/toggle`, {}, options)}
                                    className={`flex size-12 shrink-0 items-center justify-center rounded-xl border-2 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ipuc disabled:opacity-60 ${
                                        task.done ? 'border-ipuc bg-ipuc text-white' : 'border-linea bg-white'
                                    }`}
                                >
                                    {task.done && <Check aria-hidden className="size-7" />}
                                </button>
                                <span className="min-w-0 flex-1">
                                    <span className={`block text-lg ${task.done ? 'text-tinta-suave line-through' : 'font-semibold'}`}>{task.description}</span>
                                    {task.assignedTo && <span className="block text-lg text-tinta-suave">A cargo de {task.assignedTo}</span>}
                                </span>
                            </li>
                        ))}
                    </ul>
                )}

                {tasks.length === 0 && (
                    <p className="p-4 text-lg text-tinta-suave">Esta actividad todavía no tiene tareas. Con tareas, el avance se calcula solo.</p>
                )}

                {tasks.length === 0 && canCheck && (
                    <div className="border-t border-ipuc/10 p-4">
                        <button type="button" disabled={busy} onClick={() => router.post(`/activities/${activityId}/complete`, {}, options)} className={buttonClass.primary}>
                            <CircleCheckBig aria-hidden className="mr-2 size-6" />
                            Ya se hizo: marcarla cumplida
                        </button>
                    </div>
                )}

                {canEdit && <TaskAddForm activityId={activityId} />}
            </div>
        </section>
    );
}
