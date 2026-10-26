import InfoNote from '@/components/info-note';
import MemberCard from '@/components/members/member-card';
import MemberFilters from '@/components/members/member-filters';
import SearchBox from '@/components/search-box';
import MainLayout from '@/layouts/main-layout';
import { memberMatches } from '@/lib/people';
import { buttonClass } from '@/lib/styles';
import type { MemberCard as Member, MemberFilters as Filters } from '@/types/people';
import type { CommitteeOption } from '@/types/schedule';
import type { SharedProps } from '@/types/shared';
import { Link, usePage } from '@inertiajs/react';
import { EyeOff, FileWarning, ShieldAlert, UserPlus, UserX } from 'lucide-react';
import { useState } from 'react';

interface Props {
    members: Member[];
    filters: Filters;
    totalCount: number;
    missingConsent: number;
    pendingProfiles: number;
    canCreate: boolean;
    committees?: CommitteeOption[];
}

/**
 * Los miembros del alcance del cargo (HU-10). Se buscan por nombre o barrio sin importar
 * mayúsculas ni tildes, se filtran combinando, y siempre se dice cuántos cumplen sobre el total.
 */
export default function MembersIndex({ members, filters, totalCount, missingConsent, pendingProfiles, canCreate, committees }: Props) {
    const { scopeHidden } = usePage<SharedProps>().props;
    const [query, setQuery] = useState('');
    const shown = members.filter((member) => memberMatches(member, query));
    const hidden = scopeHidden?.members ?? 0;

    return (
        <MainLayout title="Miembros" subtitle={totalCount === 1 ? 'Una persona en su cargo' : `${totalCount} personas en su cargo`}>
            {canCreate && (
                <Link href="/members/create" className={`${buttonClass.primary} mb-4 lg:w-fit lg:px-6`}>
                    <UserPlus aria-hidden className="mr-2 size-6" />
                    Registrar a un hermano
                </Link>
            )}

            <div className="mb-4 flex flex-col gap-3">
                {pendingProfiles > 0 && (
                    <InfoNote icon={FileWarning} tone="gold">
                        {pendingProfiles === 1 ? 'Una ficha está' : `${pendingProfiles} fichas están`} por completar: les falta la fecha de nacimiento o la edad.
                    </InfoNote>
                )}
                {missingConsent > 0 && (
                    <InfoNote icon={ShieldAlert}>
                        {missingConsent === 1 ? 'Una ficha no tiene' : `${missingConsent} fichas no tienen`} anotada la autorización del manejo de datos.
                    </InfoNote>
                )}
                {hidden > 0 && (
                    <InfoNote icon={EyeOff}>
                        La congregación tiene {hidden === 1 ? 'un miembro más' : `${hidden} miembros más`}. No le corresponden a su cargo, por eso no aparecen aquí.
                    </InfoNote>
                )}
            </div>

            <SearchBox label="Buscar un miembro" placeholder="Nombre o barrio" value={query} onChange={setQuery} />
            <MemberFilters filters={filters} committees={committees} />

            <p aria-live="polite" className="mb-3 px-1 text-lg font-semibold">
                {shown.length} de {totalCount}
            </p>

            {shown.length === 0 ? (
                <InfoNote icon={UserX}>No hay miembros con esa búsqueda o esos filtros.</InfoNote>
            ) : (
                <ul className="grid gap-3 lg:grid-cols-2">
                    {shown.map((member) => (
                        <MemberCard key={member.id} member={member} />
                    ))}
                </ul>
            )}
        </MainLayout>
    );
}
