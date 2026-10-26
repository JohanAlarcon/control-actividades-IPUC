import CommitteeGroup from '@/components/committees/committee-group';
import ReviewerCard from '@/components/committees/reviewer-card';
import InfoNote from '@/components/info-note';
import MainLayout from '@/layouts/main-layout';
import { structureSummary } from '@/lib/committees';
import type { CommitteeNode } from '@/types/committees';
import type { SharedProps } from '@/types/shared';
import { usePage } from '@inertiajs/react';
import { EyeOff, Info } from 'lucide-react';

interface Props {
    tree: CommitteeNode[];
    reviewer: string | null;
}

export default function CommitteesIndex({ tree, reviewer }: Props) {
    const { auth, scope, scopeHidden } = usePage<SharedProps>().props;
    const hidden = scopeHidden?.committees ?? 0;

    return (
        <MainLayout title="Comités" subtitle={tree.length > 0 ? structureSummary(tree) : undefined}>
            {reviewer && <ReviewerCard name={reviewer} />}

            <div className="grid gap-4 lg:grid-cols-2 lg:items-start">
                {tree.map((root) => (
                    <CommitteeGroup key={root.id} root={root} />
                ))}
            </div>

            {tree.length === 0 && !scope.mustChoose && (
                <InfoNote icon={Info}>
                    {auth.user?.isPastor
                        ? 'Todavía no hay comités registrados.'
                        : 'Todavía no tiene cargo en ningún comité. Cuando el pastor le asigne uno, aquí verá su comité.'}
                </InfoNote>
            )}

            {/* RF-009: cuánto queda fuera del cargo, sin decir qué es. El número llega aparte, después. */}
            {hidden > 0 && (
                <div className="mt-4">
                    <InfoNote icon={EyeOff}>
                        La congregación tiene {hidden === 1 ? 'un comité más' : `${hidden} comités más`}. No le corresponden a su cargo, por
                        eso no aparecen aquí.
                    </InfoNote>
                </div>
            )}
        </MainLayout>
    );
}
