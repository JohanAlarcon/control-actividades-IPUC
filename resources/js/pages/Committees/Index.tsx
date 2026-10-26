import CommitteeTree from '@/components/committees/committee-tree';
import FlashMessages from '@/components/flash-messages';
import PageHeader from '@/components/page-header';
import ChooseRole from '@/components/scope/choose-role';
import type { CommitteeNode } from '@/types/committees';
import { Head, usePage } from '@inertiajs/react';

interface Props {
    tree: CommitteeNode[];
    reviewer: string | null;
}

type Shared = {
    auth: { user: { isPastor: boolean } };
    scope: { mustChoose: boolean };
    scopeHidden?: { committees: number };
};

export default function CommitteesIndex({ tree, reviewer }: Props) {
    const { auth, scope, scopeHidden } = usePage<Shared>().props;

    return (
        <div className="min-h-screen bg-white text-tinta">
            <Head title="Comités" />
            <PageHeader title="Comités" backHref="/dashboard" backLabel="Inicio" />

            <main className="mx-auto max-w-2xl px-4 py-6">
                <FlashMessages />
                <ChooseRole />

                {reviewer && (
                    <p className="mb-6 rounded-lg border-l-4 border-ipuc-claro bg-white py-3 pl-4 text-lg">
                        Lo que usted propone lo revisa <span className="font-bold">{reviewer}</span>, presidente de su comité.
                    </p>
                )}

                {tree.length > 0 && <CommitteeTree nodes={tree} />}
                {tree.length === 0 && !scope.mustChoose && <Empty isPastor={auth.user.isPastor} />}

                <HiddenNotice count={scopeHidden?.committees ?? 0} />
            </main>
        </div>
    );
}

function Empty({ isPastor }: { isPastor: boolean }) {
    return (
        <p className="rounded-lg border border-linea/40 p-4 text-lg">
            {isPastor
                ? 'Todavía no hay comités registrados.'
                : 'Todavía no tiene cargo en ningún comité. Cuando el pastor le asigne uno, aquí verá su comité.'}
        </p>
    );
}

/** RF-009: cuánto queda fuera del cargo, sin decir qué es. El número llega aparte, después de la pantalla. */
function HiddenNotice({ count }: { count: number }) {
    if (count === 0) return null;

    return (
        <p className="mt-6 text-lg text-tinta-suave">
            La congregación tiene {count === 1 ? 'un comité más' : `${count} comités más`}. No le corresponden a su cargo, por eso no
            aparecen aquí.
        </p>
    );
}
