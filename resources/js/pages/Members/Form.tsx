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
}

/**
 * Registrar a un hermano o corregir su ficha (HU-10), que es cosa del pastor (RF-085), con el aviso
 * de privacidad a la vista (RF-062). El código lo pone el sistema al registrarlo.
 */
export default function MemberFormPage({ member, committees }: Props) {
    return (
        <MainLayout
            title={member ? member.name : 'Registrar a un hermano'}
            subtitle={member ? [member.code && `Código ${member.code}`, ageText(member.age)].filter(Boolean).join(' · ') : 'La ficha de un miembro de la congregación'}
            back={{ href: '/members', label: 'Miembros' }}
        >
            <div className="flex flex-col gap-5 lg:max-w-2xl">
                <PrivacyNotice />
                <div className={`p-4 ${cardClass}`}>
                    <MemberForm member={member} committees={committees} />
                </div>
            </div>
        </MainLayout>
    );
}
