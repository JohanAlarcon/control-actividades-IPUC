import CommitteeIdentity from '@/components/committees/committee-identity';
import CommitteeRow from '@/components/committees/committee-row';
import InfoNote from '@/components/info-note';
import PersonRow from '@/components/person-row';
import SectionBlock from '@/components/section-block';
import MainLayout from '@/layouts/main-layout';
import { childrenTitle, levelOf, responsibleRole, structureSummary, withoutChildrenText } from '@/lib/committees';
import { roleLabelFor } from '@/lib/roles';
import { buttonClass, cardClass } from '@/lib/styles';
import type { CommitteeIdentity as Identity, CommitteeSummary } from '@/types/committees';
import { Link } from '@inertiajs/react';
import { Info, Printer } from 'lucide-react';

interface Props {
    committee: { id: number; name: string; depth: number; active: boolean };
    identity: Identity;
    canEditIdentity: boolean;
    responsible: string | null;
    secretary: string | null;
    treasurer: string | null;
    hasChildren: boolean;
    children: CommitteeSummary[];
}

export default function CommitteeShow({ committee, identity, canEditIdentity, responsible, secretary, treasurer, hasChildren, children }: Props) {
    const subtitle = [levelOf(committee.depth), hasChildren ? structureSummary(children) : null, committee.active ? null : 'ya no está activo']
        .filter(Boolean)
        .join(' · ');

    return (
        <MainLayout title={committee.name} subtitle={subtitle} back={{ href: '/committees', label: 'Comités' }}>
            <div className="lg:grid lg:grid-cols-2 lg:items-start lg:gap-6">
                <div className="lg:col-span-2">
                    <SectionBlock title="Logo, lema y objetivos">
                        <CommitteeIdentity committee={committee} identity={identity} canEdit={canEditIdentity} />
                    </SectionBlock>
                    <SectionBlock title="Sus actividades">
                        <Link href={`/print/activities?committee=${committee.id}`} className={`${buttonClass.secondary} lg:w-96`}>
                            <Printer aria-hidden className="mr-2 size-6" />
                            Imprimir o compartir
                        </Link>
                    </SectionBlock>
                </div>

                <SectionBlock title="Quiénes lo encabezan">
                    <ul className={`divide-y divide-ipuc/5 ${cardClass}`}>
                        <PersonRow role={roleLabelFor(responsibleRole(committee.depth), responsible)} name={responsible} />
                        {committee.depth === 1 && <PersonRow role={roleLabelFor('secretary', secretary)} name={secretary} />}
                        {committee.depth === 1 && <PersonRow role={roleLabelFor('treasurer', treasurer)} name={treasurer} />}
                    </ul>
                </SectionBlock>

                <SectionBlock title={hasChildren ? childrenTitle() : 'Cómo se organiza'}>
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
            </div>
        </MainLayout>
    );
}
