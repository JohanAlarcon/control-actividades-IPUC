import CommitteeRow from '@/components/committees/committee-row';
import { flattenTree, structureSummary } from '@/lib/committees';
import { cardClass } from '@/lib/styles';
import type { CommitteeNode } from '@/types/committees';

/** Un comité y todo lo que cuelga de él, en una sola tarjeta: arriba el comité, adentro sus ramas. */
export default function CommitteeGroup({ root }: { root: CommitteeNode }) {
    const branches = flattenTree(root.children);

    return (
        <section className={`overflow-hidden ${cardClass}`}>
            <CommitteeRow committee={root} prominent detail={branches.length > 0 ? structureSummary(root.children) : undefined} />

            {branches.length > 0 && (
                <ul className="divide-y divide-ipuc/5 border-t border-ipuc/10 bg-fondo/60">
                    {branches.map((branch) => (
                        <li key={branch.id} className={branch.depth - root.depth > 1 ? 'pl-10' : 'pl-4'}>
                            <CommitteeRow committee={branch} />
                        </li>
                    ))}
                </ul>
            )}
        </section>
    );
}
