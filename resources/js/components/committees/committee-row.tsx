import { responsibleRole, responsibleTitle } from '@/lib/committees';
import { roleLabelFor } from '@/lib/roles';
import type { CommitteeSummary } from '@/types/committees';
import { Link } from '@inertiajs/react';
import { ChevronRight, UserRound } from 'lucide-react';

/** Cada nivel con su color: el comité en azul, el sub-comité en celeste y el equipo en dorado. */
const TILES: Record<number, string> = {
    1: 'bg-ipuc text-white',
    2: 'bg-ipuc-claro/15 text-ipuc',
    3: 'bg-ipuc-dorado/25 text-tinta',
};

/** «Comité de Alabanza» da «A»: la letra de lo que lo distingue, no la de la palabra «Comité». */
function letterOf(name: string): string {
    const distinctive = name.replace(/^(comité|sub-comité|subcomité|equipo)\s+(de\s+(la\s+|los\s+|las\s+)?)?/i, '');

    return (distinctive[0] ?? name[0] ?? '').toUpperCase();
}

interface Props {
    committee: CommitteeSummary;
    prominent?: boolean;
    detail?: string;
}

export default function CommitteeRow({ committee, prominent = false, detail }: Props) {
    const title = responsibleTitle(committee.depth);

    return (
        <Link
            href={`/committees/${committee.id}`}
            className="flex items-center gap-3 px-4 py-3 hover:bg-ipuc/5 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-ipuc active:bg-ipuc/10"
        >
            <span
                aria-hidden
                className={`flex shrink-0 items-center justify-center rounded-xl font-bold ${prominent ? 'size-12 text-xl' : 'size-10 text-lg'} ${TILES[committee.depth] ?? TILES[1]}`}
            >
                {letterOf(committee.name)}
            </span>

            <div className="min-w-0 flex-1 break-words">
                <p className={`leading-snug font-bold ${prominent ? 'text-xl' : 'text-lg'}`}>{committee.name}</p>
                {committee.responsible ? (
                    <p className="mt-0.5 flex items-start gap-1.5 text-lg text-tinta-suave">
                        <UserRound aria-hidden className="mt-1 size-4 shrink-0" />
                        <span>
                            <span className="sr-only">{roleLabelFor(responsibleRole(committee.depth), committee.responsible)}: </span>
                            {committee.responsible}
                        </span>
                    </p>
                ) : (
                    <p className="mt-0.5 text-lg text-tinta-suave">Sin {title.toLowerCase()} asignado</p>
                )}
                {detail && <p className="mt-1 text-lg font-semibold text-ipuc">{detail}</p>}
            </div>

            <ChevronRight aria-hidden className="size-6 shrink-0 text-tinta-suave" />
        </Link>
    );
}
