import TextField from '@/components/text-field';

export type WhenField = 'date' | 'time' | 'ends';

interface Props {
    values: Record<WhenField, string>;
    limits: { min: string; max: string };
    errors: Partial<Record<WhenField, string>>;
    onChange: (field: WhenField, value: string) => void;
    /** Para que dos formularios en la misma pantalla no repitan los mismos `id`. */
    idPrefix?: string;
}

/**
 * Cuándo: la fecha y la hora de inicio y de fin. Sin la de fin no se sabe si se cruza con otra
 * (RF-028). Las horas van lado a lado solo si el formulario tiene ancho para «6:00 p. m.»: se mide
 * el formulario, no la pantalla, porque el mismo campo vive en tarjetas anchas y en filas angostas.
 */
export default function WhenFields({ values, limits, errors, onChange, idPrefix = '' }: Props) {
    return (
        <div className="@container">
            <div className="grid gap-5 @sm:grid-cols-2 @xl:grid-cols-3">
                <div className="@sm:col-span-2 @xl:col-span-1">
                    <TextField
                        id={`${idPrefix}date`}
                        type="date"
                        label="Fecha"
                        min={limits.min}
                        max={limits.max}
                        value={values.date}
                        onChange={(event) => onChange('date', event.target.value)}
                        error={errors.date}
                    />
                </div>
                <TextField
                    id={`${idPrefix}time`}
                    type="time"
                    label="Hora de inicio"
                    value={values.time}
                    onChange={(event) => onChange('time', event.target.value)}
                    error={errors.time}
                />
                <TextField
                    id={`${idPrefix}ends`}
                    type="time"
                    label="Hora de fin"
                    value={values.ends}
                    onChange={(event) => onChange('ends', event.target.value)}
                    error={errors.ends}
                />
            </div>
        </div>
    );
}
