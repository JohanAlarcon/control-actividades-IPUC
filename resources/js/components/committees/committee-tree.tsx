import CommitteeCard from '@/components/committees/committee-card';
import type { CommitteeNode } from '@/types/committees';

export default function CommitteeTree({ nodes }: { nodes: CommitteeNode[] }) {
    return (
        <ul className="flex flex-col gap-3">
            {nodes.map((node) => (
                <Branch key={node.id} node={node} />
            ))}
        </ul>
    );
}

/** Un comité y, debajo y un poco adentro, lo que cuelga de él. La sangría es corta: a 360 px caben los tres niveles. */
function Branch({ node }: { node: CommitteeNode }) {
    return (
        <li>
            <CommitteeCard committee={node} />

            {node.children.length > 0 && (
                <ul className="mt-3 ml-3 flex flex-col gap-3 border-l-2 border-ipuc-claro/40 pl-3">
                    {node.children.map((child) => (
                        <Branch key={child.id} node={child} />
                    ))}
                </ul>
            )}
        </li>
    );
}
