import CommitmentBadge from '@/components/sympathizers/commitment-badge';
import { daysAgo, STAGES } from '@/lib/people';
import { cardClass } from '@/lib/styles';
import type { SympathizerCard as Person } from '@/types/people';
import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';

/** Una persona que se acompaña: en qué etapa va, cómo va su compromiso y cuándo fue el último contacto. */
export default function SympathizerCard({ person }: { person: Person }) {
    return (
        <li>
            <Link
                href={`/sympathizers/${person.id}`}
                className={`flex h-full items-start gap-3 p-4 hover:bg-ipuc/5 focus-visible:outline-2 focus-visible:outline-ipuc active:bg-ipuc/10 ${cardClass}`}
            >
                <span className="min-w-0 flex-1">
                    <span className="block text-lg leading-snug font-bold break-words">{person.name}</span>
                    <span className="block text-lg text-tinta-suave">
                        Etapa {person.stage} de 5 · {STAGES[person.stage - 1]}
                    </span>
                    <span className="mt-2 flex flex-wrap items-center gap-2">
                        <CommitmentBadge level={person.commitment.level} />
                        <span className="text-lg text-tinta-suave">Último contacto {daysAgo(person.commitment.daysSinceContact)}</span>
                    </span>
                    {person.promotionPending && <span className="mt-2 block text-lg font-semibold text-ipuc">El paso a miembro espera al pastor</span>}
                    {!person.promotionPending && person.stage === 5 && (
                        <span className="mt-2 block text-lg font-semibold text-ipuc">Recorrió todo el camino: puede pasar a ser miembro</span>
                    )}
                </span>
                <ChevronRight aria-hidden className="mt-1 size-6 shrink-0 text-tinta-suave" />
            </Link>
        </li>
    );
}
