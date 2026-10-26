import { ACTIVITY_TYPES, type ActivityType } from '@/lib/activities';
import { errorClass } from '@/lib/styles';

interface Props {
    value: ActivityType | '';
    onChange: (type: ActivityType) => void;
    error?: string;
}

/** La clase de actividad en botones grandes, en vez de una lista que se despliega: se ven todas de una vez. */
export default function ActivityTypePicker({ value, onChange, error }: Props) {
    return (
        <fieldset id="type" tabIndex={-1} aria-invalid={error ? true : undefined} aria-describedby={error ? 'type-error' : undefined}>
            <legend className="text-lg font-semibold">¿Qué clase de actividad es?</legend>
            <div className="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-4">
                {ACTIVITY_TYPES.map((type) => {
                    const checked = value === type.value;

                    return (
                        <label
                            key={type.value}
                            className={`flex h-12 cursor-pointer items-center justify-center rounded-xl border-2 px-3 text-lg font-semibold has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-ipuc ${
                                checked ? 'border-ipuc bg-ipuc text-white' : 'border-linea bg-white text-tinta'
                            }`}
                        >
                            <input type="radio" name="type" value={type.value} checked={checked} onChange={() => onChange(type.value)} className="sr-only" />
                            {type.label}
                        </label>
                    );
                })}
            </div>
            {error && (
                <p id="type-error" className={errorClass}>
                    {error}
                </p>
            )}
        </fieldset>
    );
}
