import { responsibleTitle } from '@/lib/committees';
import type { CommitteeSummary } from '@/types/committees';
import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';

export default function CommitteeCard({ committee }: { committee: CommitteeSummary }) {
    const title = responsibleTitle(committee.depth);

    return (
        <Link
            href={`/committees/${committee.id}`}
            className="flex items-center gap-3 rounded-lg border border-linea/40 bg-white p-4 shadow-sm hover:border-ipuc focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ipuc active:bg-ipuc/5"
        >
            <div className="min-w-0 flex-1 break-words">
                <p className="text-lg leading-snug font-bold">{committee.name}</p>
                <p className="text-lg text-tinta-suave">
                    {committee.responsible ? `${title}: ${committee.responsible}` : `Sin ${title.toLowerCase()} asignado`}
                </p>
            </div>
            <ChevronRight aria-hidden className="size-6 shrink-0 text-tinta-suave" />
        </Link>
    );
}
