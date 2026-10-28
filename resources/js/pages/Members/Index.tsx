import InfoNote from '@/components/info-note';
import AddToList from '@/components/members/add-to-list';
import MemberCard from '@/components/members/member-card';
import MemberFilters from '@/components/members/member-filters';
import Pager from '@/components/pager';
import SearchBox from '@/components/search-box';
import MainLayout from '@/layouts/main-layout';
import { pageOf } from '@/lib/paging';
import { memberMatches } from '@/lib/people';
import { buttonClass } from '@/lib/styles';
import type { MemberFilters as Filters, MemberCard as Member } from '@/types/people';
import type { CommitteeOption } from '@/types/schedule';
import type { SharedProps } from '@/types/shared';
import { Link, usePage, useRemember } from '@inertiajs/react';
import { EyeOff, FileWarning, ListPlus, ShieldAlert, UserPlus, UserX } from 'lucide-react';
import { useState } from 'react';

interface Props {
    members: Member[];
    filters: Filters;
    totalCount: number;
    missingConsent: number;
    pendingProfiles: number;
    canCreate: boolean;
    canManageList: boolean;
    committees?: CommitteeOption[];
}

/**
 * Los miembros (HU-10): el pastor ve el padrón completo y registra las fichas; cada cargo ve su
 * lista y la arma con personas ya registradas (RF-085). Se buscan por nombre o barrio sin importar
 * mayúsculas ni tildes, se filtran combinando, y siempre se dice cuántos cumplen sobre el total.
 */
export default function MembersIndex({ members, filters, totalCount, missingConsent, pendingProfiles, canCreate, canManageList, committees }: Props) {
    const { scopeHidden } = usePage<SharedProps>().props;
    // La búsqueda y la página se recuerdan al volver atrás desde una ficha. La página es la de esos
    // filtros: si cambian, se vuelve a la primera.
    const [query, setQuery] = useRemember('', 'buscar-miembros');
    const filtersKey = JSON.stringify(filters);
    const [paging, setPaging] = useRemember({ filters: filtersKey, page: 1 }, 'pagina-miembros');
    const setPage = (page: number) => setPaging({ filters: filtersKey, page });
    const [adding, setAdding] = useState(false);
    const shown = members.filter((member) => memberMatches(member, query));
    const current = pageOf(shown, paging.filters === filtersKey ? paging.page : 1);
    const hidden = scopeHidden?.members ?? 0;

    return (
        <MainLayout title="Miembros" subtitle={totalCount === 1 ? 'Una persona en su cargo' : `${totalCount} personas en su cargo`}>
            {canCreate && (
                <Link href="/members/create" className={`${buttonClass.primary} mb-4 lg:w-fit lg:px-6`}>
                    <UserPlus aria-hidden className="mr-2 size-6" />
                    Registrar a un hermano
                </Link>
            )}

            {canManageList && committees && (
                <button
                    type="button"
                    aria-expanded={adding}
                    onClick={() => setAdding(!adding)}
                    className={`${adding ? buttonClass.secondary : buttonClass.primary} mb-4 lg:w-fit lg:px-6`}
                >
                    <ListPlus aria-hidden className="mr-2 size-6" />
                    {adding ? 'Cerrar' : 'Agregar a la lista'}
                </button>
            )}
            {adding && committees && <AddToList committees={committees} onDone={() => setAdding(false)} />}

            <div className="mb-4 flex flex-col gap-3">
                {pendingProfiles > 0 && (
                    <InfoNote icon={FileWarning} tone="gold">
                        {pendingProfiles === 1 ? 'Una ficha está' : `${pendingProfiles} fichas están`} por completar: les falta la fecha de nacimiento
                        o la edad.
                    </InfoNote>
                )}
                {missingConsent > 0 && (
                    <InfoNote icon={ShieldAlert}>
                        {missingConsent === 1 ? 'Una ficha no tiene' : `${missingConsent} fichas no tienen`} anotada la autorización del manejo de
                        datos.
                    </InfoNote>
                )}
                {hidden > 0 && (
                    <InfoNote icon={EyeOff}>
                        La congregación tiene {hidden === 1 ? 'un miembro más' : `${hidden} miembros más`}. No le corresponden a su cargo, por eso no
                        aparecen aquí.
                    </InfoNote>
                )}
            </div>

            <SearchBox
                label="Buscar un miembro"
                placeholder="Nombre o barrio"
                value={query}
                onChange={(value) => {
                    setQuery(value);
                    setPage(1);
                }}
            />
            <MemberFilters filters={filters} committees={committees} />

            <p id="lista-miembros" aria-live="polite" className="mb-3 scroll-mt-4 px-1 text-lg font-semibold">
                {shown.length} de {totalCount}
            </p>

            {shown.length === 0 ? (
                <InfoNote icon={UserX}>No hay miembros con esa búsqueda o esos filtros.</InfoNote>
            ) : (
                <ul className="grid gap-3 lg:grid-cols-2">
                    {current.items.map((member) => (
                        <MemberCard key={member.id} member={member} canEdit={canCreate} canManageList={canManageList} />
                    ))}
                </ul>
            )}
            <Pager page={current.page} pages={current.pages} onChange={setPage} anchor="lista-miembros" />
        </MainLayout>
    );
}
