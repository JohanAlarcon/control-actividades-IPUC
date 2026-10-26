import PrivacyNotice from '@/components/people/privacy-notice';
import SympathizerForm from '@/components/sympathizers/sympathizer-form';
import MainLayout from '@/layouts/main-layout';
import { cardClass } from '@/lib/styles';

/** Registrar a alguien que visitó la iglesia (HU-13), con el aviso de privacidad a la vista (RF-062). */
export default function SympathizerFormPage() {
    return (
        <MainLayout title="Registrar a un simpatizante" subtitle="Alguien que visitó la iglesia, para que no se pierda de vista" back={{ href: '/dashboard', label: 'Inicio' }}>
            <div className="flex flex-col gap-5 lg:max-w-2xl">
                <PrivacyNotice />
                <div className={`p-4 ${cardClass}`}>
                    <SympathizerForm />
                </div>
            </div>
        </MainLayout>
    );
}
