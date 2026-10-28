import CheckField from '@/components/check-field';
import type { MemberData } from '@/components/members/member-form';
import TextField from '@/components/text-field';
import { ADULT_ONLY, MARITAL_STATUSES, maritalLabel } from '@/lib/people';
import { errorClass, fieldClass } from '@/lib/styles';
import type { MaritalStatus } from '@/types/people';
import type { InertiaFormProps } from '@inertiajs/react';

interface Props {
    form: InertiaFormProps<MemberData>;
    /** Sin fecha de nacimiento ni edad anotada: entonces se confirma la mayoría de edad (RF-032). */
    noAge: boolean;
    today: string;
}

/** El estado civil, el bautismo en el nombre de Jesucristo y la fecha de ingreso (RF-031). */
export default function MemberStatusFields({ form, noAge, today }: Props) {
    const errors = form.errors as Partial<Record<keyof MemberData, string>>;

    return (
        <>
            <div>
                <label htmlFor="marital_status" className="block text-lg font-semibold">
                    Estado civil
                </label>
                <select
                    id="marital_status"
                    value={form.data.marital_status}
                    onChange={(event) => form.setData('marital_status', event.target.value as MaritalStatus)}
                    aria-invalid={errors.marital_status ? true : undefined}
                    className={fieldClass}
                >
                    {MARITAL_STATUSES.map((status) => (
                        <option key={status} value={status}>
                            {maritalLabel(status, form.data.gender || null)}
                        </option>
                    ))}
                </select>
                {errors.marital_status && <p className={errorClass}>{errors.marital_status}</p>}
            </div>

            {noAge && ADULT_ONLY.includes(form.data.marital_status) && (
                <CheckField
                    id="adult_confirmed"
                    label="Confirmo que es mayor de dieciocho años"
                    checked={form.data.adult_confirmed}
                    onChange={(checked) => form.setData('adult_confirmed', checked)}
                    error={errors.adult_confirmed}
                />
            )}

            <CheckField id="baptized" label="Está bautizado en el nombre de Jesucristo" checked={form.data.baptized} onChange={(checked) => form.setData('baptized', checked)} />
            {form.data.baptized && (
                <TextField
                    id="baptized_at"
                    type="date"
                    label="Fecha del bautismo"
                    max={today}
                    value={form.data.baptized_at}
                    onChange={(event) => form.setData('baptized_at', event.target.value)}
                    error={errors.baptized_at}
                />
            )}

            <TextField
                id="joined_at"
                type="date"
                label="Fecha de ingreso a la congregación (si la sabe)"
                max={today}
                value={form.data.joined_at}
                onChange={(event) => form.setData('joined_at', event.target.value)}
                error={errors.joined_at}
            />
        </>
    );
}
