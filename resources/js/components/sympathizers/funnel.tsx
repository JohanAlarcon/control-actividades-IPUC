import ProgressBar from '@/components/progress-bar';
import { STAGES } from '@/lib/people';
import { cardClass } from '@/lib/styles';

/** El embudo de las cinco etapas: cuántas personas llegaron a cada una. En vertical, para leerse en el celular. */
export default function Funnel({ funnel }: { funnel: { stage: number; count: number }[] }) {
    const first = funnel[0]?.count || 1;

    return (
        <section aria-labelledby="camino" className="mb-6">
            <h2 id="camino" className="mb-3 px-1 text-xl font-bold">
                El camino
            </h2>
            <ol className={`flex flex-col gap-4 p-4 ${cardClass}`}>
                {funnel.map((step) => (
                    <li key={step.stage}>
                        <p className="flex items-baseline justify-between gap-3 text-lg">
                            <span className="min-w-0">
                                <span className="font-bold text-ipuc">{step.stage}.</span> {STAGES[step.stage - 1]}
                            </span>
                            <span className="shrink-0 text-xl font-bold">{step.count}</span>
                        </p>
                        <div className="mt-2">
                            <ProgressBar value={Math.round((step.count / first) * 100)} label={`${step.count} personas en «${STAGES[step.stage - 1]}»`} size="sm" />
                        </div>
                    </li>
                ))}
            </ol>
        </section>
    );
}
