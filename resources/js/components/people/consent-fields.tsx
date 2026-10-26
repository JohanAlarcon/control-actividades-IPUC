import ChoiceChips from '@/components/people/choice-chips';
import TextField from '@/components/text-field';
import { calendarDate } from '@/lib/format';
import { CONSENT_MEANS } from '@/lib/people';
import type { ConsentMeans } from '@/types/people';

interface Props {
    consent: boolean;
    at: string;
    means: ConsentMeans | '';
    onConsent: (value: boolean) => void;
    onAt: (value: string) => void;
    onMeans: (value: ConsentMeans) => void;
    errors: { data_consent_at?: string; data_consent_means?: string };
}

/** Si la persona autorizó el manejo de sus datos, en qué fecha y cómo (RF-061). */
export default function ConsentFields({ consent, at, means, onConsent, onAt, onMeans, errors }: Props) {
    return (
        <div className="flex flex-col gap-4 rounded-2xl bg-fondo p-4">
            <label className="flex min-h-12 cursor-pointer items-center gap-3 text-lg font-semibold">
                <input
                    type="checkbox"
                    checked={consent}
                    onChange={(event) => {
                        onConsent(event.target.checked);
                        if (event.target.checked && !at) onAt(calendarDate(new Date()));
                    }}
                    className="size-6 shrink-0 accent-ipuc"
                />
                La persona autorizó el manejo de sus datos
            </label>

            {consent && (
                <>
                    <TextField
                        id="data_consent_at"
                        type="date"
                        label="¿Cuándo lo autorizó?"
                        max={calendarDate(new Date())}
                        value={at}
                        onChange={(event) => onAt(event.target.value)}
                        error={errors.data_consent_at}
                    />
                    <ChoiceChips
                        id="data_consent_means"
                        legend="¿Cómo lo autorizó?"
                        options={CONSENT_MEANS}
                        value={means}
                        onChange={onMeans}
                        error={errors.data_consent_means}
                        columns={3}
                    />
                </>
            )}
        </div>
    );
}
