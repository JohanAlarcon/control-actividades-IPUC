import { errorClass } from '@/lib/styles';
import type { CommitteeOption } from '@/types/schedule';

interface Props {
    committees: CommitteeOption[];
    value: number[];
    onChange: (ids: number[]) => void;
    error?: string;
}

/**
 * Los comités a los que pertenece la persona (D-32): puede estar en varios, o en ninguno todavía.
 * Su función en cada uno —directiva, maestro, auxiliar o integrante— la anota después ese comité.
 */
export default function CommitteeChecklist({ committees, value, onChange, error }: Props) {
    function toggle(id: number, checked: boolean) {
        onChange(checked ? [...value, id] : value.filter((other) => other !== id));
    }

    return (
        <fieldset>
            <legend className="text-lg font-semibold">Comités a los que pertenece (opcional)</legend>
            <p className="mt-1 text-lg text-tinta-suave">Puede estar en varios. Cada comité anota después su función en la lista.</p>
            <ul className="mt-3 flex flex-col gap-1 rounded-2xl border border-linea/40 p-2">
                {committees.map((committee) => (
                    <li key={committee.id} className={committee.depth > 1 ? 'pl-6' : ''}>
                        <label className="flex min-h-12 cursor-pointer items-center gap-3 rounded-xl px-2 text-lg hover:bg-ipuc/5">
                            <input
                                type="checkbox"
                                checked={value.includes(committee.id)}
                                onChange={(event) => toggle(committee.id, event.target.checked)}
                                className="size-6 shrink-0 accent-ipuc"
                            />
                            {committee.name}
                        </label>
                    </li>
                ))}
            </ul>
            {error && <p className={errorClass}>{error}</p>}
        </fieldset>
    );
}
