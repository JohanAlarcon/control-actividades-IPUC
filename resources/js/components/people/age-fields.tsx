import TextField from '@/components/text-field';
import { calendarDate } from '@/lib/format';
import type { AgeMode } from '@/types/people';

interface Props {
    mode: AgeMode;
    birthDate: string;
    ageReported: string;
    onMode: (mode: AgeMode) => void;
    onBirthDate: (value: string) => void;
    onAgeReported: (value: string) => void;
    errors: { birth_date?: string; age_reported?: string };
}

/**
 * La fecha de nacimiento, o «No la sé — anote la edad» (RF-031). Lo que la congregación tiene
 * escrito muchas veces es la edad: se guarda con el día en que se anota, para que siga al día.
 */
export default function AgeFields({ mode, birthDate, ageReported, onMode, onBirthDate, onAgeReported, errors }: Props) {
    const switchClass = 'mt-2 inline-flex min-h-12 items-center text-lg font-semibold text-ipuc underline underline-offset-4';

    if (mode === 'age') {
        return (
            <div>
                <TextField
                    id="age_reported"
                    label="¿Cuántos años tiene?"
                    hint="Se guarda con la fecha de hoy, para que la edad siga al día."
                    inputMode="numeric"
                    autoComplete="off"
                    value={ageReported}
                    onChange={(event) => onAgeReported(event.target.value.replace(/\D/g, '').slice(0, 3))}
                    error={errors.age_reported}
                />
                <button type="button" onClick={() => onMode('date')} className={switchClass}>
                    Mejor, escoger la fecha de nacimiento
                </button>
            </div>
        );
    }

    return (
        <div>
            <TextField
                id="birth_date"
                type="date"
                label="Fecha de nacimiento (si la sabe)"
                max={calendarDate(new Date())}
                value={birthDate}
                onChange={(event) => onBirthDate(event.target.value)}
                error={errors.birth_date}
            />
            <button type="button" onClick={() => onMode('age')} className={switchClass}>
                No la sé — anote la edad
            </button>
        </div>
    );
}
