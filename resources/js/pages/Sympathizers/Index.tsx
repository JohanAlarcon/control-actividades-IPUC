import InfoNote from '@/components/info-note';
import Pager from '@/components/pager';
import ByCommittee, { type CommitteeCount } from '@/components/sympathizers/by-committee';
import Funnel from '@/components/sympathizers/funnel';
import SympathizerCard from '@/components/sympathizers/sympathizer-card';
import MainLayout from '@/layouts/main-layout';
import { pageOf } from '@/lib/paging';
import { buttonClass, cardClass } from '@/lib/styles';
import type { SympathizerCard as Person } from '@/types/people';
import { Link, useRemember } from '@inertiajs/react';
import { ShieldAlert, UserPlus, Users } from 'lucide-react';

interface Props {
    /** El cargo activo es de BIS, que recibe a los simpatizantes y los asigna (D-34). */
    isReception: boolean;
    stats: { total: number; growing: number; stable: number; drifting: number };
    funnel: { stage: number; count: number }[];
    list: Person[];
    missingConsent: number;
    /** Solo para BIS y el pastor, que ven toda la sede. */
    byCommittee: CommitteeCount[] | null;
}

/**
 * Los simpatizantes (HU-14): cuántos son, cómo van y el embudo. BIS y el pastor ven toda la sede y
 * cuántos lleva cada comité; un comité, solo los asignados a él (RF-040).
 */
export default function SympathizersIndex({ isReception, stats, funnel, list, missingConsent, byCommittee }: Props) {
    const [page, setPage] = useRemember(1, 'pagina-simpatizantes');
    const current = pageOf(list, page);
    const tiles = [
        { label: 'Acompañamos', value: stats.total, tone: 'text-tinta' },
        { label: 'Creciendo', value: stats.growing, tone: 'text-ipuc' },
        { label: 'Estables', value: stats.stable, tone: 'text-ipuc' },
        { label: 'Alejándose', value: stats.drifting, tone: 'text-alerta' },
    ];

    return (
        <MainLayout title="Simpatizantes" subtitle={isReception ? 'Las personas que llegan a la iglesia' : 'Las personas que acompaña su comité'}>
            <Link href="/sympathizers/create" className={`${buttonClass.primary} mb-6 lg:w-fit lg:px-6`}>
                <UserPlus aria-hidden className="mr-2 size-6" />
                Registrar a alguien que nos visitó
            </Link>

            <dl className="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
                {tiles.map((tile) => (
                    <div key={tile.label} className={`p-4 ${cardClass}`}>
                        <dt className="text-lg text-tinta-suave">{tile.label}</dt>
                        <dd className={`text-3xl font-bold ${tile.tone}`}>{tile.value}</dd>
                    </div>
                ))}
            </dl>

            {missingConsent > 0 && (
                <div className="mb-6">
                    <InfoNote icon={ShieldAlert}>
                        {missingConsent === 1 ? 'Una ficha no tiene' : `${missingConsent} fichas no tienen`} anotada la autorización del manejo de
                        datos.
                    </InfoNote>
                </div>
            )}

            {byCommittee && <ByCommittee rows={byCommittee} />}

            <div className="lg:grid lg:grid-cols-[1fr_2fr] lg:items-start lg:gap-6">
                <Funnel funnel={funnel} />

                <section aria-labelledby="personas" className="mb-6">
                    <h2 id="personas" className="mb-3 scroll-mt-4 px-1 text-xl font-bold">
                        Cada persona
                    </h2>
                    {list.length === 0 ? (
                        <InfoNote icon={Users}>
                            {isReception
                                ? 'Todavía no hay simpatizantes registrados.'
                                : 'Su comité todavía no tiene simpatizantes asignados. Los que registre quedan en su lista.'}
                        </InfoNote>
                    ) : (
                        <ul className="grid gap-3 xl:grid-cols-2">
                            {current.items.map((person) => (
                                <SympathizerCard key={person.id} person={person} showCommittee={byCommittee !== null} />
                            ))}
                        </ul>
                    )}
                    <Pager page={current.page} pages={current.pages} onChange={setPage} anchor="personas" />
                </section>
            </div>
        </MainLayout>
    );
}
