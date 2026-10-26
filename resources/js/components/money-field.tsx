import { groupThousands } from '@/lib/format';
import { errorClass, fieldClass } from '@/lib/styles';

interface Props {
    id: string;
    label: string;
    hint?: string;
    value: string;
    onChange: (value: string) => void;
    error?: string;
}

/** Un campo de pesos: el signo adelante y los puntos de mil puestos mientras se escribe («350.000»). */
export default function MoneyField({ id, label, hint, value, onChange, error }: Props) {
    return (
        <div>
            <label htmlFor={id} className="block text-lg font-semibold">
                {label}
            </label>
            <div className="relative">
                <span aria-hidden className="pointer-events-none absolute top-1/2 left-4 mt-1 -translate-y-1/2 text-lg text-tinta-suave">
                    $
                </span>
                <input
                    id={id}
                    inputMode="numeric"
                    autoComplete="off"
                    value={value}
                    onChange={(event) => onChange(groupThousands(event.target.value))}
                    aria-describedby={hint ? `${id}-ayuda` : undefined}
                    aria-invalid={error ? true : undefined}
                    className={`${fieldClass} pl-9`}
                />
            </div>
            {hint && (
                <p id={`${id}-ayuda`} className="mt-2 text-lg text-tinta-suave">
                    {hint}
                </p>
            )}
            {error && <p className={errorClass}>{error}</p>}
        </div>
    );
}
