import CommitteeCard from '@/components/committees/committee-card';
import PageHeader from '@/components/page-header';
import { childrenText, levelOf, responsibleTitle, withoutChildrenText } from '@/lib/committees';
import { ROLE_LABELS } from '@/lib/roles';
import type { CommitteeSummary } from '@/types/committees';
import { Head } from '@inertiajs/react';

interface Props {
    committee: { id: number; name: string; depth: number; active: boolean };
    responsible: string | null;
    secretary: string | null;
    treasurer: string | null;
    hasChildren: boolean;
    children: CommitteeSummary[];
}

export default function CommitteeShow({ committee, responsible, secretary, treasurer, hasChildren, children }: Props) {
    return (
        <div className="min-h-screen bg-white text-tinta">
            <Head title={committee.name} />
            <PageHeader title={committee.name} backHref="/committees" backLabel="Comités" />

            <main className="mx-auto max-w-2xl px-4 py-6">
                <p className="text-lg text-tinta-suave">
                    {levelOf(committee.depth)}
                    {!committee.active && ' · ya no está activo'}
                </p>

                <section aria-labelledby="encabezan" className="mt-4">
                    <h2 id="encabezan" className="text-xl font-bold">
                        Quiénes lo encabezan
                    </h2>
                    <dl className="mt-3 flex flex-col gap-3">
                        <Holder title={responsibleTitle(committee.depth)} name={responsible} />
                        {committee.depth === 1 && <Holder title={ROLE_LABELS.secretary} name={secretary} />}
                        {committee.depth === 1 && <Holder title={ROLE_LABELS.treasurer} name={treasurer} />}
                    </dl>
                </section>

                <section aria-labelledby="organiza" className="mt-8 border-t border-linea/40 pt-6">
                    <h2 id="organiza" className="text-xl font-bold">
                        Cómo se organiza
                    </h2>
                    {hasChildren ? (
                        <>
                            <p className="mt-3 text-lg">{childrenText(committee.depth, children.length)}</p>
                            <ul className="mt-3 flex flex-col gap-3">
                                {children.map((child) => (
                                    <li key={child.id}>
                                        <CommitteeCard committee={child} />
                                    </li>
                                ))}
                            </ul>
                        </>
                    ) : (
                        <p className="mt-3 text-lg">{withoutChildrenText(committee.depth)}</p>
                    )}
                </section>
            </main>
        </div>
    );
}

function Holder({ title, name }: { title: string; name: string | null }) {
    return (
        <div>
            <dt className="text-lg text-tinta-suave">{title}</dt>
            <dd className={`text-lg ${name ? 'font-semibold' : 'text-tinta-suave'}`}>{name ?? 'Sin asignar'}</dd>
        </div>
    );
}
