import { errorClass } from '@/lib/styles';

interface Props {
    id: string;
    label: string;
    checked: boolean;
    onChange: (checked: boolean) => void;
    error?: string;
}

/** Una casilla grande, con su frase al lado: se toca toda la fila, no solo el cuadrito. */
export default function CheckField({ id, label, checked, onChange, error }: Props) {
    return (
        <div>
            <label htmlFor={id} className="flex min-h-12 cursor-pointer items-center gap-3 text-lg font-semibold">
                <input
                    id={id}
                    type="checkbox"
                    checked={checked}
                    onChange={(event) => onChange(event.target.checked)}
                    aria-invalid={error ? true : undefined}
                    className="size-6 shrink-0 accent-ipuc"
                />
                {label}
            </label>
            {error && <p className={errorClass}>{error}</p>}
        </div>
    );
}
