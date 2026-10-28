import AgeFields from '@/components/people/age-fields';
import ChoiceChips from '@/components/people/choice-chips';
import ConsentFields from '@/components/people/consent-fields';
import ArrivalFields from '@/components/sympathizers/arrival-fields';
import CommitteeChoice, { type CommitteeOption } from '@/components/sympathizers/committee-choice';
import DuplicateWarning from '@/components/sympathizers/duplicate-warning';
import NameWithSuggestions from '@/components/sympathizers/name-with-suggestions';
import TextField from '@/components/text-field';
import { calendarDate } from '@/lib/format';
import { GENDERS } from '@/lib/people';
import { buttonClass } from '@/lib/styles';
import type { AgeMode, ConsentMeans, Gender } from '@/types/people';
import { Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

type Data = {
    name: string;
    age_mode: AgeMode;
    birth_date: string;
    age_reported: string;
    gender: Gender | '';
    phone: string;
    neighborhood: string;
    arrival_source: string;
    invited_by_name: string;
    first_contact_at: string;
    committee_id: number | '';
    data_consent: boolean;
    data_consent_at: string;
    data_consent_means: ConsentMeans | '';
};

interface Props {
    committees: CommitteeOption[] | null;
    ownCommittee: string | null;
}

const blank = (today: string): Data => ({
    name: '',
    age_mode: 'date',
    birth_date: '',
    age_reported: '',
    gender: '',
    phone: '',
    neighborhood: '',
    arrival_source: '',
    invited_by_name: '',
    first_contact_at: today,
    committee_id: '',
    data_consent: false,
    data_consent_at: '',
    data_consent_means: '',
});

/**
 * Registrar a alguien que visitó la iglesia (HU-13). BIS escoge el comité que la acompañará; un
 * comité la deja en su lista (D-34). Si ya hay alguien con ese nombre y ese teléfono, avisa antes de
 * guardar y deja decidir, sin perder lo escrito (CL-09).
 */
export default function SympathizerForm({ committees, ownCommittee }: Props) {
    const today = calendarDate(new Date());
    const form = useForm<Data>(blank(today));
    const errors = form.errors as Partial<Record<keyof Data | 'duplicate', string>>;

    function send(insist: boolean) {
        form.transform(({ committee_id, ...data }) => ({ ...data, ...(committees ? { committee_id } : {}), confirm_duplicate: insist }));
        form.post('/sympathizers', {
            onError: (found) => {
                if (!('duplicate' in found)) document.getElementById(Object.keys(found)[0])?.focus();
            },
        });
    }

    function submit(event: FormEvent) {
        event.preventDefault();
        send(false);
    }

    return (
        <form onSubmit={submit} noValidate className="flex flex-col gap-5">
            <NameWithSuggestions value={form.data.name} onChange={(name) => form.setData('name', name)} error={errors.name} />
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
            <TextField id="phone" type="tel" label="Teléfono (si lo dio)" value={form.data.phone} onChange={(event) => form.setData('phone', event.target.value)} error={errors.phone} />
            <TextField id="neighborhood" label="Barrio (si lo sabe)" value={form.data.neighborhood} onChange={(event) => form.setData('neighborhood', event.target.value)} error={errors.neighborhood} />
            <ArrivalFields
                source={form.data.arrival_source}
                invitedBy={form.data.invited_by_name}
                firstContact={form.data.first_contact_at}
                today={today}
                onSource={(value) => form.setData('arrival_source', value)}
                onInvitedBy={(value) => form.setData('invited_by_name', value)}
                onFirstContact={(value) => form.setData('first_contact_at', value)}
                errors={errors}
            />
            <CommitteeChoice committees={committees} ownCommittee={ownCommittee} value={form.data.committee_id} onChange={(id) => form.setData('committee_id', id)} error={errors.committee_id} />
            <ConsentFields
                consent={form.data.data_consent}
                at={form.data.data_consent_at}
                means={form.data.data_consent_means}
                onConsent={(value) => form.setData('data_consent', value)}
                onAt={(value) => form.setData('data_consent_at', value)}
                onMeans={(value) => form.setData('data_consent_means', value)}
                errors={errors}
            />

            {errors.duplicate ? <DuplicateWarning message={errors.duplicate} busy={form.processing} onInsist={() => send(true)} /> : <Actions busy={form.processing} />}
        </form>
    );
}

function Actions({ busy }: { busy: boolean }) {
    return (
        <div className="mt-2 flex flex-col gap-3">
            <button type="submit" disabled={busy} className={buttonClass.primary}>
                Registrarla
            </button>
            <Link href="/sympathizers" className={buttonClass.secondary}>
                Cancelar
            </Link>
        </div>
    );
}
