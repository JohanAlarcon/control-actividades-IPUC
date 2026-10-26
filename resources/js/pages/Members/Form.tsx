import MemberForm from '@/components/members/member-form';
import PrivacyNotice from '@/components/people/privacy-notice';
import MainLayout from '@/layouts/main-layout';
import { ageText } from '@/lib/people';
import { cardClass } from '@/lib/styles';
import type { MemberDetail } from '@/types/people';
import type { CommitteeOption } from '@/types/schedule';

interface Props {
    member: MemberDetail | null;
    committees: CommitteeOption[];
    canLeaveWithoutCommittee: boolean;
}

/** Registrar a un hermano o corregir su ficha (HU-10), con el aviso de privacidad a la vista (RF-062). */
export default function MemberFormPage({ member, committees, canLeaveWithoutCommittee }: Props) {
    return (
        <MainLayout
            title={member ? member.name : 'Registrar a un hermano'}
            subtitle={member ? ageText(member.age) : 'La ficha de un miembro de la congregación'}
            back={{ href: '/members', label: 'Miembros' }}
        >
            <div className="flex flex-col gap-5 lg:max-w-2xl">
                <PrivacyNotice />
                <div className={`p-4 ${cardClass}`}>
                    <MemberForm member={member} committees={committees} canLeaveWithoutCommittee={canLeaveWithoutCommittee} />
                </div>
            </div>
        </MainLayout>
    );
}
