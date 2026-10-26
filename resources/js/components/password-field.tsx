import { errorClass, iconFieldClass } from '@/lib/styles';
import { Eye, EyeOff, LockKeyhole } from 'lucide-react';
import { useState } from 'react';

interface Props {
    id: string;
    label: string;
    value: string;
    onChange: (value: string) => void;
    autoComplete: 'current-password' | 'new-password';
    autoFocus?: boolean;
    hint?: string;
    error?: string;
}

/** Un campo de clave con su ojito: quien escribe despacio puede revisar lo que escribió antes de enviarlo. */
export default function PasswordField({ id, label, value, onChange, autoComplete, autoFocus, hint, error }: Props) {
    const [visible, setVisible] = useState(false);
    const described = [hint && `${id}-hint`, error && `${id}-error`].filter(Boolean).join(' ');

    return (
        <div>
            <label htmlFor={id} className="block text-lg font-semibold">
                {label}
            </label>
            <div className="relative mt-2">
                <LockKeyhole aria-hidden className="pointer-events-none absolute top-1/2 left-4 size-6 -translate-y-1/2 text-tinta-suave" />
                <input
                    id={id}
                    type={visible ? 'text' : 'password'}
                    autoComplete={autoComplete}
                    autoCapitalize="none"
                    autoCorrect="off"
                    spellCheck={false}
                    autoFocus={autoFocus}
                    value={value}
                    onChange={(event) => onChange(event.target.value)}
                    aria-invalid={error ? true : undefined}
                    aria-describedby={described || undefined}
                    className={`${iconFieldClass} pr-14`}
                />
                <button
                    type="button"
                    onClick={() => setVisible(!visible)}
                    aria-controls={id}
                    aria-label={visible ? 'Ocultar la clave' : 'Mostrar la clave'}
                    className="absolute top-1/2 right-1 flex size-12 -translate-y-1/2 items-center justify-center rounded-xl text-tinta-suave hover:text-ipuc focus-visible:outline-2 focus-visible:outline-ipuc"
                >
                    {visible ? <EyeOff aria-hidden className="size-6" /> : <Eye aria-hidden className="size-6" />}
                </button>
            </div>
            {hint && (
                <p id={`${id}-hint`} className="mt-2 text-lg text-tinta-suave">
                    {hint}
                </p>
            )}
            {error && (
                <p id={`${id}-error`} className={errorClass}>
                    {error}
                </p>
            )}
        </div>
    );
}
