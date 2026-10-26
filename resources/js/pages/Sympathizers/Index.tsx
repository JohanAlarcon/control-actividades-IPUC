import InfoNote from '@/components/info-note';
import Funnel from '@/components/sympathizers/funnel';
import SympathizerCard from '@/components/sympathizers/sympathizer-card';
import MainLayout from '@/layouts/main-layout';
import { buttonClass, cardClass } from '@/lib/styles';
import type { SympathizerCard as Person } from '@/types/people';
import { Link } from '@inertiajs/react';
import { HeartHandshake, ShieldAlert, UserPlus, Users } from 'lucide-react';

interface Props {
    canFollow: boolean;
    /** Para quien no acompaña: el comité que sí lo hace, para explicarle. */
    followers?: string | null;
    stats?: { total: number; growing: number; stable: number; drifting: number };
    funnel?: { stage: number; count: number }[];
    list?: Person[];
    missingConsent?: number;
}

/**
 * Los simpatizantes (HU-14). Quien acompaña ve cuántos son, cómo van y el embudo. A los demás se
 * les explica por qué no ven las fichas, sin que parezca un error (HU-13, escenario 2).
 */
export default function SympathizersIndex({ canFollow, followers, stats, funnel = [], list = [], missingConsent = 0 }: Props) {
    const register = (
        <Link href="/sympathizers/create" className={`${buttonClass.primary} mb-6 lg:w-fit lg:px-6`}>
            <UserPlus aria-hidden className="mr-2 size-6" />
            Registrar a alguien que nos visitó
        </Link>
    );

    if (!canFollow) {
        return (
            <MainLayout title="Simpatizantes" subtitle="Las personas que visitan la iglesia">
                {register}
                <InfoNote icon={HeartHandshake}>
                    Usted puede registrar a quien invite o visite la iglesia. El seguimiento de cada persona lo lleva la presidencia del{' '}
                    {followers ?? 'Comité de Evangelismo'}, con el pastor: por eso las fichas no aparecen aquí.
                </InfoNote>
            </MainLayout>
        );
    }

    const tiles = [
        { label: 'Acompañamos', value: stats?.total ?? 0, tone: 'text-tinta' },
        { label: 'Creciendo', value: stats?.growing ?? 0, tone: 'text-ipuc' },
        { label: 'Estables', value: stats?.stable ?? 0, tone: 'text-ipuc' },
        { label: 'Alejándose', value: stats?.drifting ?? 0, tone: 'text-alerta' },
    ];

    return (
        <MainLayout title="Simpatizantes" subtitle="Las personas que acompañamos">
            {register}

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
                        {missingConsent === 1 ? 'Una ficha no tiene' : `${missingConsent} fichas no tienen`} anotada la autorización del manejo de datos.
                    </InfoNote>
                </div>
            )}

            <div className="lg:grid lg:grid-cols-[1fr_2fr] lg:items-start lg:gap-6">
                <Funnel funnel={funnel} />

                <section aria-labelledby="personas" className="mb-6">
                    <h2 id="personas" className="mb-3 px-1 text-xl font-bold">
                        Cada persona
                    </h2>
                    {list.length === 0 ? (
                        <InfoNote icon={Users}>Todavía no hay simpatizantes registrados.</InfoNote>
                    ) : (
                        <ul className="grid gap-3 xl:grid-cols-2">
                            {list.map((person) => (
                                <SympathizerCard key={person.id} person={person} />
                            ))}
                        </ul>
                    )}
                </section>
            </div>
        </MainLayout>
    );
}
