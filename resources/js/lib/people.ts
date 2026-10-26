import { matchesEveryWord } from '@/lib/search';
import type { CommitmentLevel, Gender, MaritalStatus, MemberFilters } from '@/types/people';

export const GENDERS = [
    { value: 'f', label: 'Mujer' },
    { value: 'm', label: 'Hombre' },
] as const;

export const MARITAL_STATUSES: MaritalStatus[] = ['single', 'married', 'widowed', 'separated', 'common_law'];

/** Lo que un menor de dieciocho años no puede figurar (RF-032). La misma regla del servidor. */
export const ADULT_ONLY: MaritalStatus[] = ['married', 'widowed', 'separated', 'common_law'];

const MARITAL: Record<MaritalStatus, { f: string; m: string; any: string }> = {
    single: { f: 'Soltera', m: 'Soltero', any: 'Soltero o soltera' },
    married: { f: 'Casada', m: 'Casado', any: 'Casado o casada' },
    widowed: { f: 'Viuda', m: 'Viudo', any: 'Viudo o viuda' },
    separated: { f: 'Separada', m: 'Separado', any: 'Separado o separada' },
    common_law: { f: 'En unión libre', m: 'En unión libre', any: 'En unión libre' },
};

/** El estado civil dicho como corresponde a la persona; sin saber si es hombre o mujer, las dos formas. */
export function maritalLabel(status: MaritalStatus, gender?: Gender | null): string {
    return MARITAL[status][gender ?? 'any'];
}

export const CONSENT_MEANS = [
    { value: 'signature', label: 'Firmando' },
    { value: 'verbal', label: 'De palabra, en el culto' },
    { value: 'message', label: 'Por mensaje' },
] as const;

export const AGE_RANGES = [
    { value: 'children', label: 'Niños (0 a 12)' },
    { value: 'teens', label: 'Adolescentes (13 a 17)' },
    { value: 'young', label: 'Jóvenes (18 a 29)' },
    { value: 'adults', label: 'Adultos (30 a 59)' },
    { value: 'seniors', label: 'Adultos mayores (60 o más)' },
] as const;

export const ARRIVAL_SOURCES = [
    { value: 'invitation', label: 'Lo invitaron' },
    { value: 'refam', label: 'Por la REFAM' },
    { value: 'campaign', label: 'En una campaña' },
    { value: 'family', label: 'Por su familia' },
    { value: 'neighbor', label: 'Por un vecino' },
    { value: 'media', label: 'Por radio o redes' },
] as const;

/** Las cinco etapas del camino, en orden y con el vocabulario de la IPUC (RF-042). */
export const STAGES = ['Visitó el culto', 'Asiste con frecuencia', 'Recibe estudio bíblico', 'Se bautizó en el nombre de Jesucristo', 'Recibió el Espíritu Santo'];

export const VISIT_TYPES = [
    { value: 'service', label: 'Vino al culto' },
    { value: 'visit', label: 'Visita' },
    { value: 'call', label: 'Llamada' },
    { value: 'study', label: 'Estudio bíblico' },
] as const;

export const COMMITMENT_INFO: Record<CommitmentLevel, { label: string; tone: string }> = {
    growing: { label: 'Creciendo', tone: 'bg-ipuc text-white' },
    stable: { label: 'Estable', tone: 'bg-ipuc-claro/20 text-ipuc' },
    drifting: { label: 'Alejándose', tone: 'bg-alerta/10 text-alerta' },
};

/** «42 años»; sin fecha ni edad anotada, se dice que falta, en vez de mostrar cero. */
export function ageText(age: number | null): string {
    if (age === null) return 'Edad por completar';

    return `${age} ${age === 1 ? 'año' : 'años'}`;
}

/** «hoy», «ayer» o «hace 12 días». */
export function daysAgo(days: number): string {
    if (days <= 0) return 'hoy';
    if (days === 1) return 'ayer';

    return `hace ${days} días`;
}

/** La dirección de la lista de miembros con sus filtros, sin los vacíos. */
export function membersUrl(filters: Partial<MemberFilters>): string {
    const query = Object.entries(filters)
        .filter(([, value]) => value !== null && value !== undefined)
        .map(([key, value]) => `${key}=${encodeURIComponent(String(value))}`)
        .join('&');

    return query ? `/members?${query}` : '/members';
}

/** La búsqueda de miembros: por nombre o por barrio, sin importar mayúsculas ni tildes (HU-10, escenario 4). */
export function memberMatches(member: { name: string; neighborhood: string | null }, query: string): boolean {
    return matchesEveryWord(`${member.name} ${member.neighborhood ?? ''}`, query);
}
