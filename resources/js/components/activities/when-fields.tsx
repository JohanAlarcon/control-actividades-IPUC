import TextField from '@/components/text-field';

export type WhenField = 'date' | 'time' | 'ends';

interface Props {
    values: Record<WhenField, string>;
    limits: { min: string; max: string };
    errors: Partial<Record<WhenField, string>>;
    onChange: (field: WhenField, value: string) => void;
}

/** Cuándo: la fecha y la hora de inicio y de fin. Sin la de fin no se sabe si se cruza con otra (RF-028). */
export default function WhenFields({ values, limits, errors, onChange }: Props) {
    return (
        <div className="grid grid-cols-2 gap-5 lg:grid-cols-3">
            <div className="col-span-2 lg:col-span-1">
                <TextField
                    id="date"
                    type="date"
                    label="Fecha"
                    min={limits.min}
                    max={limits.max}
                    value={values.date}
                    onChange={(event) => onChange('date', event.target.value)}
                    error={errors.date}
                />
            </div>
            <TextField id="time" type="time" label="Hora de inicio" value={values.time} onChange={(event) => onChange('time', event.target.value)} error={errors.time} />
            <TextField id="ends" type="time" label="Hora de fin" value={values.ends} onChange={(event) => onChange('ends', event.target.value)} error={errors.ends} />
        </div>
    );
}
