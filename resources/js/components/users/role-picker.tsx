import { roleLabelFor, rolesForDepth } from '@/lib/roles';
import { errorClass, fieldClass } from '@/lib/styles';
import type { CommitteeOption } from '@/types/accounts';

interface Props {
    committees: CommitteeOption[];
    committeeId: string;
    role: string;
    errors: { committee_id?: string; role?: string };
    onChange: (committeeId: string, role: string) => void;
    optional?: boolean;
    personName?: string;
}

export default function RolePicker({ committees, committeeId, role, errors, onChange, optional = false, personName }: Props) {
    const chosen = committees.find((committee) => String(committee.id) === committeeId);
    const roles = chosen ? rolesForDepth(chosen.depth) : [];

    const changeCommittee = (id: string) => {
        const committee = committees.find((option) => String(option.id) === id);
        const possible = committee ? rolesForDepth(committee.depth) : [];

        // Un sub-comité solo tiene líder: no hay nada que escoger.
        onChange(id, possible.length === 1 ? possible[0] : '');
    };

    return (
        <>
            <div>
                <label htmlFor="committee_id" className="block text-lg font-semibold">
                    Comité {optional && <span className="font-normal text-tinta-suave">(puede dejarlo para después)</span>}
                </label>
                <select id="committee_id" value={committeeId} onChange={(e) => changeCommittee(e.target.value)} className={fieldClass}>
                    <option value="">{optional ? 'Sin cargo por ahora' : 'Escoja un comité'}</option>
                    {committees
                        .filter((committee) => committee.active)
                        .map((committee) => (
                            <option key={committee.id} value={committee.id}>
                                {'— '.repeat(committee.depth - 1) + committee.name}
                            </option>
                        ))}
                </select>
                {errors.committee_id && <p className={errorClass}>{errors.committee_id}</p>}
            </div>

            {chosen && (
                <div>
                    <label htmlFor="role" className="block text-lg font-semibold">
                        Cargo
                    </label>
                    <select id="role" value={role} onChange={(e) => onChange(committeeId, e.target.value)} className={fieldClass}>
                        {roles.length > 1 && <option value="">Escoja el cargo</option>}
                        {roles.map((option) => (
                            <option key={option} value={option}>
                                {roleLabelFor(option, personName)}
                            </option>
                        ))}
                    </select>
                    {errors.role && <p className={errorClass}>{errors.role}</p>}
                </div>
            )}
        </>
    );
}
