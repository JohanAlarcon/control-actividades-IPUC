import InitialsAvatar from '@/components/initials-avatar';
import MembershipChip from '@/components/members/membership-chip';
import { ageText, maritalLabel } from '@/lib/people';
import { cardClass } from '@/lib/styles';
import type { MemberCard as Member } from '@/types/people';
import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';

interface Props {
    member: Member;
    /** El pastor abre la ficha para corregirla; los demás solo la ven (RF-085). */
    canEdit: boolean;
    canManageList: boolean;
}

/**
 * Un miembro en la lista: su edad calculada, su estado civil, las listas de su ámbito en que está
 * con su función, y lo que le falta a su ficha. Quien no está en ninguna lista se señala como
 * oportunidad, no como error (CL-03).
 */
export default function MemberCard({ member, canEdit, canManageList }: Props) {
    const baptized = member.gender === 'f' ? 'Bautizada' : 'Bautizado';
    const summary = (
        <>
            <InitialsAvatar name={member.name} size="md" tone="light" />
            <span className="min-w-0 flex-1">
                <span className="block text-lg leading-snug font-bold break-words">{member.name}</span>
                <span className="block text-lg text-tinta-suave">
                    {ageText(member.age)} · {maritalLabel(member.maritalStatus, member.gender)}
                    {member.baptized && ` · ${baptized}`}
                </span>
                {member.code && <span className="block text-lg text-tinta-suave">Código {member.code}</span>}
                <span className="mt-2 flex flex-wrap gap-2">
                    {member.committees.length === 0 && <Flag tone="gold">Sin comité: puede integrarse a uno</Flag>}
                    {member.profilePending && <Flag tone="gold">Ficha por completar</Flag>}
                    {!member.dataConsent && <Flag tone="alert">Sin autorización de datos</Flag>}
                </span>
            </span>
        </>
    );

    return (
        <li className={`flex h-full flex-col ${cardClass}`}>
            {canEdit ? (
                <Link href={`/members/${member.id}/edit`} className="flex items-start gap-3 rounded-2xl p-4 hover:bg-ipuc/5 focus-visible:outline-2 focus-visible:outline-ipuc active:bg-ipuc/10">
                    {summary}
                    <ChevronRight aria-hidden className="mt-1 size-6 shrink-0 text-tinta-suave" />
                </Link>
            ) : (
                <div className="flex items-start gap-3 p-4">{summary}</div>
            )}
            {member.committees.length > 0 && (
                <div className="flex flex-wrap gap-2 px-4 pb-4">
                    {member.committees.map((membership) => (
                        <MembershipChip key={membership.id} memberId={member.id} memberName={member.name} gender={member.gender} membership={membership} canRemove={canManageList} />
                    ))}
                </div>
            )}
        </li>
    );
}

function Flag({ tone, children }: { tone: 'gold' | 'alert'; children: string }) {
    return (
        <span className={`inline-flex rounded-full px-3 py-0.5 text-lg font-semibold ${tone === 'gold' ? 'bg-ipuc-dorado/25 text-tinta' : 'bg-alerta/10 text-alerta'}`}>
            {children}
        </span>
    );
}
