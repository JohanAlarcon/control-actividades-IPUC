import InitialsAvatar from '@/components/initials-avatar';
import { ageText, maritalLabel } from '@/lib/people';
import { cardClass } from '@/lib/styles';
import type { MemberCard as Member } from '@/types/people';
import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';

/**
 * Un miembro en la lista: su edad calculada, su estado civil y su comité, y lo que le falta a su
 * ficha. Quien no está en ningún comité se señala como oportunidad, no como error (CL-03).
 */
export default function MemberCard({ member }: { member: Member }) {
    const baptized = member.gender === 'f' ? 'Bautizada' : 'Bautizado';

    return (
        <li>
            <Link
                href={`/members/${member.id}/edit`}
                className={`flex h-full items-start gap-3 p-4 hover:bg-ipuc/5 focus-visible:outline-2 focus-visible:outline-ipuc active:bg-ipuc/10 ${cardClass}`}
            >
                <InitialsAvatar name={member.name} size="md" tone="light" />
                <span className="min-w-0 flex-1">
                    <span className="block text-lg leading-snug font-bold break-words">{member.name}</span>
                    <span className="block text-lg text-tinta-suave">
                        {ageText(member.age)} · {maritalLabel(member.maritalStatus, member.gender)}
                        {member.baptized && ` · ${baptized}`}
                    </span>
                    {member.committee && <span className="block text-lg text-tinta-suave">{member.committee}</span>}

                    <span className="mt-2 flex flex-wrap gap-2">
                        {!member.committee && <Flag tone="gold">Sin comité: puede integrarse a uno</Flag>}
                        {member.profilePending && <Flag tone="gold">Ficha por completar</Flag>}
                        {!member.dataConsent && <Flag tone="alert">Sin autorización de datos</Flag>}
                    </span>
                </span>
                <ChevronRight aria-hidden className="mt-1 size-6 shrink-0 text-tinta-suave" />
            </Link>
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
