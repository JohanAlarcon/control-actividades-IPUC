import AgeFields from '@/components/people/age-fields';
import ChoiceChips from '@/components/people/choice-chips';
import ConsentFields from '@/components/people/consent-fields';
import TextField from '@/components/text-field';
import { calendarDate } from '@/lib/format';
import { ARRIVAL_SOURCES, GENDERS } from '@/lib/people';
import { buttonClass } from '@/lib/styles';
import type { AgeMode, ConsentMeans, Gender } from '@/types/people';
import { Link, useForm } from '@inertiajs/react';
import { TriangleAlert } from 'lucide-react';
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
    data_consent: boolean;
    data_consent_at: string;
    data_consent_means: ConsentMeans | '';
};

/**
 * Registrar a alguien que visitó la iglesia (HU-13). Si ya hay una persona con ese nombre y ese
 * teléfono, avisa antes de guardar y deja decidir, sin perder lo escrito (CL-09).
 */
export default function SympathizerForm() {
    const today = calendarDate(new Date());
    const form = useForm<Data>({
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
        data_consent: false,
        data_consent_at: '',
        data_consent_means: '',
    });
    const errors = form.errors as Partial<Record<keyof Data | 'duplicate', string>>;

    function send(insist: boolean) {
        form.transform((data) => ({ ...data, confirm_duplicate: insist }));
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
            <TextField id="name" label="Nombre" autoComplete="off" value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} error={errors.name} />

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

            <ChoiceChips
                id="arrival_source"
                legend="¿Cómo llegó a la iglesia?"
                options={ARRIVAL_SOURCES}
                value={form.data.arrival_source}
                onChange={(value) => form.setData('arrival_source', value)}
                error={errors.arrival_source}
                columns={3}
            />

            <TextField
                id="invited_by_name"
                label="¿Quién lo invitó? (si no fue usted)"
                hint="Usted queda anotado como quien lo registró."
                value={form.data.invited_by_name}
                onChange={(event) => form.setData('invited_by_name', event.target.value)}
                error={errors.invited_by_name}
            />

            <TextField
                id="first_contact_at"
                type="date"
                label="¿Cuándo vino por primera vez?"
                max={today}
                value={form.data.first_contact_at}
                onChange={(event) => form.setData('first_contact_at', event.target.value)}
                error={errors.first_contact_at}
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

            {errors.duplicate ? (
                <div role="alert" className="rounded-2xl bg-ipuc-dorado/20 p-4 ring-1 ring-ipuc-dorado/60">
                    <p className="flex items-center gap-2 text-lg font-bold">
                        <TriangleAlert aria-hidden className="size-6 shrink-0" />
                        Puede que ya esté registrada
                    </p>
                    <p className="mt-2 text-lg leading-relaxed">{errors.duplicate}</p>
                    <div className="mt-4 grid gap-3 sm:grid-cols-2">
                        <Link href="/dashboard" className={buttonClass.secondary}>
                            No registrarla
                        </Link>
                        <button type="button" onClick={() => send(true)} disabled={form.processing} className={buttonClass.primary}>
                            Es otra persona: registrarla
                        </button>
                    </div>
                </div>
            ) : (
                <div className="mt-2 flex flex-col gap-3">
                    <button type="submit" disabled={form.processing} className={buttonClass.primary}>
                        Registrarla
                    </button>
                    <Link href="/dashboard" className={buttonClass.secondary}>
                        Cancelar
                    </Link>
                </div>
            )}
        </form>
    );
}
