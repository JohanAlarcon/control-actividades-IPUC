import { errorClass, fieldClass } from '@/lib/styles';
import type { InputHTMLAttributes } from 'react';

interface Props extends Omit<InputHTMLAttributes<HTMLInputElement>, 'className'> {
    id: string;
    label: string;
    hint?: string;
    error?: string;
}

export default function TextField({ id, label, hint, error, ...props }: Props) {
    return (
        <div>
            <label htmlFor={id} className="block text-lg font-semibold">
                {label}
            </label>
            <input
                id={id}
                aria-describedby={hint ? `${id}-ayuda` : undefined}
                aria-invalid={error ? true : undefined}
                className={fieldClass}
                {...props}
            />
            {hint && (
                <p id={`${id}-ayuda`} className="mt-2 text-lg text-tinta-suave">
                    {hint}
                </p>
            )}
            {error && <p className={errorClass}>{error}</p>}
        </div>
    );
}
