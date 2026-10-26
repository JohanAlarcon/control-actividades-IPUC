import CommitteeRow from '@/components/committees/committee-row';
import InfoNote from '@/components/info-note';
import PersonRow from '@/components/person-row';
import SectionBlock from '@/components/section-block';
import MainLayout from '@/layouts/main-layout';
import { childrenTitle, levelOf, responsibleTitle, structureSummary, withoutChildrenText } from '@/lib/committees';
import { ROLE_LABELS } from '@/lib/roles';
import { cardClass } from '@/lib/styles';
import type { CommitteeSummary } from '@/types/committees';
import { Info } from 'lucide-react';

interface Props {
    committee: { id: number; name: string; depth: number; active: boolean };
    responsible: string | null;
    secretary: string | null;
    treasurer: string | null;
    hasChildren: boolean;
    children: CommitteeSummary[];
}

export default function CommitteeShow({ committee, responsible, secretary, treasurer, hasChildren, children }: Props) {
    const subtitle = [levelOf(committee.depth), hasChildren ? structureSummary(children) : null, committee.active ? null : 'ya no está activo']
        .filter(Boolean)
        .join(' · ');

    return (
        <MainLayout title={committee.name} subtitle={subtitle} back={{ href: '/committees', label: 'Comités' }}>
            <SectionBlock title="Quiénes lo encabezan">
                <ul className={`divide-y divide-ipuc/5 ${cardClass}`}>
                    <PersonRow role={responsibleTitle(committee.depth)} name={responsible} />
                    {committee.depth === 1 && <PersonRow role={ROLE_LABELS.secretary} name={secretary} />}
                    {committee.depth === 1 && <PersonRow role={ROLE_LABELS.treasurer} name={treasurer} />}
                </ul>
            </SectionBlock>

            <SectionBlock title={hasChildren ? childrenTitle(committee.depth) : 'Cómo se organiza'}>
                {hasChildren ? (
                    <ul className={`divide-y divide-ipuc/5 overflow-hidden ${cardClass}`}>
                        {children.map((child) => (
                            <li key={child.id}>
                                <CommitteeRow committee={child} />
                            </li>
                        ))}
                    </ul>
                ) : (
                    <InfoNote icon={Info}>{withoutChildrenText(committee.depth)}</InfoNote>
                )}
            </SectionBlock>
        </MainLayout>
    );
}
