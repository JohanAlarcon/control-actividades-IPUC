import InfoNote from '@/components/info-note';
import { errorClass, fieldClass } from '@/lib/styles';
import { HeartHandshake } from 'lucide-react';

export interface CommitteeOption {
    id: number;
    name: string;
}

interface Props {
    /** La lista para asignar, solo para BIS y el pastor (excepción 2). */
    committees: CommitteeOption[] | null;
    /** Para un comité: el suyo, donde queda la persona. */
    ownCommittee: string | null;
    value: number | '';
    onChange: (id: number | '') => void;
    error?: string;
}

/**
 * Qué comité la va a acompañar (D-34): BIS lo escoge según la persona; un comité registra a sus
 * amigos y quedan en su lista.
 */
export default function CommitteeChoice({ committees, ownCommittee, value, onChange, error }: Props) {
    if (!committees) {
        return <InfoNote icon={HeartHandshake}>La persona queda en la lista de {ownCommittee ?? 'su comité'}, que la acompañará. BIS también la ve.</InfoNote>;
    }

    return (
        <div>
            <label htmlFor="committee_id" className="block text-lg font-semibold">
                Comité que la va a acompañar
            </label>
            <select
                id="committee_id"
                value={value}
                onChange={(event) => onChange(event.target.value ? Number(event.target.value) : '')}
                aria-invalid={error ? true : undefined}
                className={fieldClass}
            >
                <option value="">Escoja el comité</option>
                {committees.map((committee) => (
                    <option key={committee.id} value={committee.id}>
                        {committee.name}
                    </option>
                ))}
            </select>
            {error && <p className={errorClass}>{error}</p>}
        </div>
    );
}
