import TextField from '@/components/text-field';
import { errorClass, fieldClass } from '@/lib/styles';
import type { Highlight } from '@/types/schedule';

interface Props {
    theme: string;
    highlight: Highlight | '';
    errors: { theme?: string; highlight?: string };
    onTheme: (theme: string) => void;
    onHighlight: (highlight: Highlight | '') => void;
}

const OPTIONS: { value: Highlight | ''; label: string }[] = [
    { value: '', label: 'Como las demás' },
    { value: 'special_day', label: 'Día especial de la congregación, en rojo' },
    { value: 'district', label: 'Actividad del distrito, en naranja' },
    { value: 'national', label: 'Actividad nacional, en naranja' },
];

/** Cómo sale en el cronograma de la sede (RF-116): el tema del culto, en azul, y su marca de color. */
export default function HighlightFields({ theme, highlight, errors, onTheme, onHighlight }: Props) {
    return (
        <>
            <TextField
                id="theme"
                label="Tema o lema (opcional)"
                hint="Sale en azul, debajo del nombre. Por ejemplo: Alabadle al son de bocina."
                autoComplete="off"
                maxLength={120}
                value={theme}
                onChange={(event) => onTheme(event.target.value)}
                error={errors.theme}
            />

            <div>
                <label htmlFor="highlight" className="block text-lg font-semibold">
                    Cómo sale en el cronograma
                </label>
                <select
                    id="highlight"
                    value={highlight}
                    onChange={(event) => onHighlight(event.target.value as Highlight | '')}
                    aria-describedby="highlight-ayuda"
                    aria-invalid={errors.highlight ? true : undefined}
                    className={fieldClass}
                >
                    {OPTIONS.map((option) => (
                        <option key={option.value} value={option.value}>
                            {option.label}
                        </option>
                    ))}
                </select>
                <p id="highlight-ayuda" className="mt-2 text-lg text-tinta-suave">
                    El día misionero o la Canasta de amor van en rojo; un encuentro o un torneo del distrito, en naranja.
                </p>
                {errors.highlight && <p className={errorClass}>{errors.highlight}</p>}
            </div>
        </>
    );
}
