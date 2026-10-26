import AddTypeForm from '@/components/activity-types/add-type-form';
import TypeRow from '@/components/activity-types/type-row';
import SectionBlock from '@/components/section-block';
import MainLayout from '@/layouts/main-layout';
import { cardClass } from '@/lib/styles';
import type { ManagedActivityType } from '@/types/activities';

/** Las clases de actividad de la congregación, que administra el pastor (D-25). */
export default function ActivityTypesIndex({ types }: { types: ManagedActivityType[] }) {
    const offered = types.filter((type) => type.active);
    const inactive = types.filter((type) => !type.active);

    return (
        <MainLayout title="Clases de actividad" subtitle="Las que se ofrecen al proponer una actividad" back={{ href: '/dashboard', label: 'Inicio' }}>
            <div className="lg:max-w-2xl">
                <SectionBlock title="Agregar una clase">
                    <div className={`p-4 ${cardClass}`}>
                        <AddTypeForm />
                    </div>
                </SectionBlock>

                <SectionBlock title={`Se ofrecen (${offered.length})`}>
                    <TypeList types={offered} />
                </SectionBlock>

                {inactive.length > 0 && (
                    <SectionBlock title={`Desactivadas (${inactive.length})`}>
                        <p className="mb-3 px-1 text-lg text-tinta-suave">No se ofrecen al proponer. Las actividades que ya las tienen no cambian.</p>
                        <TypeList types={inactive} />
                    </SectionBlock>
                )}
            </div>
        </MainLayout>
    );
}

function TypeList({ types }: { types: ManagedActivityType[] }) {
    return (
        <ul className={`divide-y divide-ipuc/5 ${cardClass}`}>
            {types.map((type) => (
                <TypeRow key={type.id} type={type} />
            ))}
        </ul>
    );
}
