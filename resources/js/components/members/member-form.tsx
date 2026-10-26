import CheckField from '@/components/check-field';
import AgeFields from '@/components/people/age-fields';
import ChoiceChips from '@/components/people/choice-chips';
import ConsentFields from '@/components/people/consent-fields';
import TextField from '@/components/text-field';
import { calendarDate } from '@/lib/format';
import { ADULT_ONLY, GENDERS, MARITAL_STATUSES, maritalLabel } from '@/lib/people';
import { buttonClass, errorClass, fieldClass } from '@/lib/styles';
import type { AgeMode, ConsentMeans, Gender, MaritalStatus, MemberDetail } from '@/types/people';
import type { CommitteeOption } from '@/types/schedule';
import { Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

type Data = {
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
    committee_id: number | '';
    phone: string;
    neighborhood: string;
    data_consent: boolean;
    data_consent_at: string;
    data_consent_means: ConsentMeans | '';
};

interface Props {
    member: MemberDetail | null;
    committees: CommitteeOption[];
    canLeaveWithoutCommittee: boolean;
}

/**
 * La ficha de un miembro (HU-10). Nunca exige un dato que la congregación no tiene: sin fecha ni
 * edad, basta con confirmar que es mayor de edad, y la ficha queda por completar (RF-032).
 */
export default function MemberForm({ member, committees, canLeaveWithoutCommittee }: Props) {
    const form = useForm<Data>({
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
        committee_id: member?.committeeId ?? (committees.length === 1 ? committees[0].id : ''),
        phone: member?.phone ?? '',
        neighborhood: member?.neighborhood ?? '',
        data_consent: member?.dataConsent ?? false,
        data_consent_at: member?.dataConsentAt ?? '',
        data_consent_means: member?.dataConsentMeans ?? '',
    });
    const errors = form.errors as Partial<Record<keyof Data, string>>;
    const noAge = form.data.age_mode === 'date' ? !form.data.birth_date : !form.data.age_reported;
    const today = calendarDate(new Date());

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

            <div>
                <label htmlFor="marital_status" className="block text-lg font-semibold">
                    Estado civil
                </label>
                <select
                    id="marital_status"
                    value={form.data.marital_status}
                    onChange={(event) => form.setData('marital_status', event.target.value as MaritalStatus)}
                    aria-invalid={errors.marital_status ? true : undefined}
                    className={fieldClass}
                >
                    {MARITAL_STATUSES.map((status) => (
                        <option key={status} value={status}>
                            {maritalLabel(status, form.data.gender || null)}
                        </option>
                    ))}
                </select>
                {errors.marital_status && <p className={errorClass}>{errors.marital_status}</p>}
            </div>

            {noAge && ADULT_ONLY.includes(form.data.marital_status) && (
                <CheckField
                    id="adult_confirmed"
                    label="Confirmo que es mayor de dieciocho años"
                    checked={form.data.adult_confirmed}
                    onChange={(checked) => form.setData('adult_confirmed', checked)}
                    error={errors.adult_confirmed}
                />
            )}

            <CheckField id="baptized" label="Está bautizado en el nombre de Jesucristo" checked={form.data.baptized} onChange={(checked) => form.setData('baptized', checked)} />
            {form.data.baptized && (
                <TextField
                    id="baptized_at"
                    type="date"
                    label="Fecha del bautismo"
                    max={today}
                    value={form.data.baptized_at}
                    onChange={(event) => form.setData('baptized_at', event.target.value)}
                    error={errors.baptized_at}
                />
            )}

            <TextField
                id="joined_at"
                type="date"
                label="Fecha de ingreso a la congregación (si la sabe)"
                max={today}
                value={form.data.joined_at}
                onChange={(event) => form.setData('joined_at', event.target.value)}
                error={errors.joined_at}
            />

            <div>
                <label htmlFor="committee_id" className="block text-lg font-semibold">
                    Comité, sub-comité o equipo
                </label>
                <select
                    id="committee_id"
                    value={form.data.committee_id}
                    onChange={(event) => form.setData('committee_id', event.target.value ? Number(event.target.value) : '')}
                    aria-invalid={errors.committee_id ? true : undefined}
                    className={fieldClass}
                >
                    <option value="">{canLeaveWithoutCommittee ? 'Sin comité todavía' : 'Escoja uno'}</option>
                    {committees.map((committee) => (
                        <option key={committee.id} value={committee.id}>
                            {'— '.repeat(committee.depth - 1)}
                            {committee.name}
                        </option>
                    ))}
                </select>
                {errors.committee_id && <p className={errorClass}>{errors.committee_id}</p>}
            </div>

            <TextField id="phone" type="tel" label="Teléfono (opcional)" value={form.data.phone} onChange={(event) => form.setData('phone', event.target.value)} error={errors.phone} />
            <TextField
                id="neighborhood"
                label="Barrio (opcional)"
                value={form.data.neighborhood}
                onChange={(event) => form.setData('neighborhood', event.target.value)}
                error={errors.neighborhood}
            />

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
