import PrivacyNotice from '@/components/people/privacy-notice';
import type { CommitteeOption } from '@/components/sympathizers/committee-choice';
import SympathizerForm from '@/components/sympathizers/sympathizer-form';
import MainLayout from '@/layouts/main-layout';
import { cardClass } from '@/lib/styles';

interface Props {
    /** Para BIS y el pastor: los comités entre los que se escoge quién la acompaña (excepción 2). */
    committees: CommitteeOption[] | null;
    /** Para un comité: el suyo, donde queda la persona. */
    ownCommittee: string | null;
}

/** Registrar a alguien que visitó la iglesia (HU-13), con el aviso de privacidad a la vista (RF-062). */
export default function SympathizerFormPage({ committees, ownCommittee }: Props) {
    return (
        <MainLayout title="Registrar a un simpatizante" subtitle="Alguien que visitó la iglesia, para que no se pierda de vista" back={{ href: '/sympathizers', label: 'Simpatizantes' }}>
            <div className="flex flex-col gap-5 lg:max-w-2xl">
                <PrivacyNotice />
                <div className={`p-4 ${cardClass}`}>
                    <SympathizerForm committees={committees} ownCommittee={ownCommittee} />
                </div>
            </div>
        </MainLayout>
    );
}
