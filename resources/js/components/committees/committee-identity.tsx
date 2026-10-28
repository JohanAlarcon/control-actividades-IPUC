import CommitteeBadge from '@/components/committees/committee-badge';
import { shortDate } from '@/lib/format';
import { buttonClass, cardClass } from '@/lib/styles';
import type { CommitteeIdentity as Identity } from '@/types/committees';
import { Link } from '@inertiajs/react';

interface Props {
    committee: { id: number; name: string };
    identity: Identity;
    canEdit: boolean;
}

/**
 * El logo, el lema y los objetivos en la ficha del comité (RF-083). Los ve quien tiene el comité en
 * su ámbito; los cambia su presidente (HU-26 escenarios 1 y 5).
 */
export default function CommitteeIdentity({ committee, identity, canEdit }: Props) {
    const empty = !identity.logoUrl && !identity.motto && !identity.objectives;

    return (
        <div className={`flex flex-col gap-4 p-4 ${cardClass}`}>
            <div className="flex items-center gap-4">
                <CommitteeBadge name={committee.name} logoUrl={identity.logoUrl} />
                {identity.motto ? (
                    <p className="min-w-0 text-xl font-semibold break-words text-ipuc">«{identity.motto}»</p>
                ) : (
                    <p className="min-w-0 text-lg text-tinta-suave">{empty ? 'Todavía no tiene logo, lema ni objetivos.' : 'Todavía no tiene lema.'}</p>
                )}
            </div>

            {identity.objectives && (
                <div>
                    <h3 className="text-lg font-bold">Objetivos</h3>
                    <p className="text-lg break-words whitespace-pre-line">{identity.objectives}</p>
                </div>
            )}

            {identity.updatedBy && identity.updatedAt && (
                <p className="text-lg text-tinta-suave">
                    Los registró {identity.updatedBy} el {shortDate(identity.updatedAt)}.
                </p>
            )}

            {canEdit && (
                <Link href={`/committees/${committee.id}/identity`} className={buttonClass.secondary}>
                    {empty ? 'Registrar la identidad' : 'Cambiar la identidad'}
                </Link>
            )}
        </div>
    );
}
