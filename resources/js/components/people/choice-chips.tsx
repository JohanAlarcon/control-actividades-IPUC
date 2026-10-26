import { errorClass } from '@/lib/styles';

interface Props<T extends string> {
    id: string;
    legend: string;
    options: readonly { value: T; label: string }[];
    value: T | '' | null;
    onChange: (value: T) => void;
    error?: string;
    columns?: 2 | 3;
}

/** Una pregunta de pocas respuestas, en botones grandes: se ven todas de una vez y se tocan fácil. */
export default function ChoiceChips<T extends string>({ id, legend, options, value, onChange, error, columns = 2 }: Props<T>) {
    return (
        <fieldset id={id} tabIndex={-1} aria-invalid={error ? true : undefined} aria-describedby={error ? `${id}-error` : undefined}>
            <legend className="text-lg font-semibold">{legend}</legend>
            <div className={`mt-2 grid grid-cols-2 gap-2 ${columns === 3 ? 'sm:grid-cols-3' : ''}`}>
                {options.map((option) => {
                    const checked = value === option.value;

                    return (
                        <label
                            key={option.value}
                            className={`flex min-h-12 cursor-pointer items-center justify-center rounded-xl border-2 px-3 py-1 text-center text-lg font-semibold has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-ipuc ${
                                checked ? 'border-ipuc bg-ipuc text-white' : 'border-linea bg-white text-tinta'
                            }`}
                        >
                            <input type="radio" name={id} value={option.value} checked={checked} onChange={() => onChange(option.value)} className="sr-only" />
                            {option.label}
                        </label>
                    );
                })}
            </div>
            {error && (
                <p id={`${id}-error`} className={errorClass}>
                    {error}
                </p>
            )}
        </fieldset>
    );
}
