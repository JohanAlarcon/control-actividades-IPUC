import CommitteeChecklist from '@/components/members/committee-checklist';
import MemberStatusFields from '@/components/members/member-status-fields';
import AgeFields from '@/components/people/age-fields';
import ChoiceChips from '@/components/people/choice-chips';
import ConsentFields from '@/components/people/consent-fields';
import TextAreaField from '@/components/text-area-field';
import TextField from '@/components/text-field';
import { calendarDate } from '@/lib/format';
import { GENDERS } from '@/lib/people';
import { buttonClass } from '@/lib/styles';
import type { AgeMode, ConsentMeans, Gender, MaritalStatus, MemberDetail } from '@/types/people';
import type { CommitteeOption } from '@/types/schedule';
import { Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

export type MemberData = {
    name: string;
    age_mode: AgeMode;
    birth_date: string;
    age_reported: string;
    gender: Gender | '';
    marital_status: MaritalStatus;
    adult_confirmed: boolean;
    baptized: boolean;
    baptized_at: string;
    joined_at: string;
    committee_ids: number[];
    phone: string;
    neighborhood: string;
    notes: string;
    data_consent: boolean;
    data_consent_at: string;
    data_consent_means: ConsentMeans | '';
};

/**
 * La ficha de un miembro (HU-10), que registra y corrige el pastor (RF-085). Nunca exige un dato
 * que la congregación no tiene: sin fecha ni edad, basta con confirmar que es mayor de edad, y la
 * ficha queda por completar (RF-032). La dirección no se pide (RF-031).
 */
export default function MemberForm({ member, committees }: { member: MemberDetail | null; committees: CommitteeOption[] }) {
    const form = useForm<MemberData>({
        name: member?.name ?? '',
        age_mode: member?.ageMode ?? 'date',
        birth_date: member?.birthDate ?? '',
        age_reported: member?.ageReported?.toString() ?? '',
        gender: member?.gender ?? '',
        marital_status: member?.maritalStatus ?? 'single',
        adult_confirmed: member?.adultConfirmed ?? false,
        baptized: member?.baptized ?? false,
        baptized_at: member?.baptizedAt ?? '',
        joined_at: member?.joinedAt ?? '',
        committee_ids: member?.committeeIds ?? [],
        phone: member?.phone ?? '',
        neighborhood: member?.neighborhood ?? '',
        notes: member?.notes ?? '',
        data_consent: member?.dataConsent ?? false,
        data_consent_at: member?.dataConsentAt ?? '',
        data_consent_means: member?.dataConsentMeans ?? '',
    });
    const errors = form.errors as Partial<Record<keyof MemberData, string>>;

    function submit(event: FormEvent) {
        event.preventDefault();
        const options = { onError: (found: Record<string, string>) => document.getElementById(Object.keys(found)[0])?.focus() };

        if (member) form.put(`/members/${member.id}`, options);
        else form.post('/members', options);
    }

    return (
        <form onSubmit={submit} noValidate className="flex flex-col gap-5">
            <TextField
                id="name"
                label="Nombre"
                hint="Como se le dice en la iglesia. Por ejemplo: Hermana Luz Mary Quintero."
                autoComplete="off"
                value={form.data.name}
                onChange={(event) => form.setData('name', event.target.value)}
                error={errors.name}
            />
            <AgeFields
                mode={form.data.age_mode}
                birthDate={form.data.birth_date}
                ageReported={form.data.age_reported}
                onMode={(mode) => form.setData('age_mode', mode)}
                onBirthDate={(value) => form.setData('birth_date', value)}
                onAgeReported={(value) => form.setData('age_reported', value)}
                errors={errors}
            />
            <ChoiceChips id="gender" legend="¿Es hombre o mujer?" options={GENDERS} value={form.data.gender} onChange={(value) => form.setData('gender', value)} error={errors.gender} />
            <MemberStatusFields form={form} noAge={form.data.age_mode === 'date' ? !form.data.birth_date : !form.data.age_reported} today={calendarDate(new Date())} />
            <CommitteeChecklist committees={committees} value={form.data.committee_ids} onChange={(ids) => form.setData('committee_ids', ids)} error={errors.committee_ids} />
            <TextField id="phone" type="tel" label="Teléfono (opcional)" value={form.data.phone} onChange={(event) => form.setData('phone', event.target.value)} error={errors.phone} />
            <TextField id="neighborhood" label="Barrio (opcional)" value={form.data.neighborhood} onChange={(event) => form.setData('neighborhood', event.target.value)} error={errors.neighborhood} />
            <TextAreaField id="notes" label="Observaciones (opcional)" value={form.data.notes} onChange={(event) => form.setData('notes', event.target.value)} error={errors.notes} />
            <ConsentFields
                consent={form.data.data_consent}
                at={form.data.data_consent_at}
                means={form.data.data_consent_means}
                onConsent={(value) => form.setData('data_consent', value)}
                onAt={(value) => form.setData('data_consent_at', value)}
                onMeans={(value) => form.setData('data_consent_means', value)}
                errors={errors}
            />

            <div className="mt-2 flex flex-col gap-3">
                <button type="submit" disabled={form.processing} className={buttonClass.primary}>
                    {member ? 'Guardar los cambios' : 'Registrarlo'}
                </button>
                <Link href="/members" className={buttonClass.secondary}>
                    Cancelar
                </Link>
            </div>
        </form>
    );
}
