import { errorClass } from '@/lib/styles';
import type { ActivityTypeOption } from '@/types/activities';

interface Props {
    types: ActivityTypeOption[];
    value: number | '';
    onChange: (id: number) => void;
    error?: string;
}

/**
 * La clase de actividad en botones grandes, en vez de una lista que se despliega: se ven todas de
 * una vez. Son las que el pastor tiene activas (D-25).
 */
export default function ActivityTypePicker({ types, value, onChange, error }: Props) {
    return (
        <fieldset
            id="activity_type_id"
            tabIndex={-1}
            aria-invalid={error ? true : undefined}
            aria-describedby={error ? 'activity_type_id-error' : undefined}
        >
            <legend className="text-lg font-semibold">¿Qué clase de actividad es?</legend>
            <div className="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-4">
                {types.map((type) => {
                    const checked = value === type.id;

                    return (
                        <label
                            key={type.id}
                            className={`flex min-h-12 cursor-pointer items-center justify-center rounded-xl border-2 px-3 py-1 text-center text-lg font-semibold has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-ipuc ${
                                checked ? 'border-ipuc bg-ipuc text-white' : 'border-linea bg-white text-tinta'
                            }`}
                        >
                            <input type="radio" name="activity_type_id" value={type.id} checked={checked} onChange={() => onChange(type.id)} className="sr-only" />
                            {type.name}
                        </label>
                    );
                })}
            </div>
            {error && (
                <p id="activity_type_id-error" className={errorClass}>
                    {error}
                </p>
            )}
        </fieldset>
    );
}
