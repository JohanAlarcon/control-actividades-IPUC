import SearchBox from '@/components/search-box';
import { useMemberPicker, type PickerResult } from '@/hooks/use-member-picker';
import { ageText, BOARD_POSITIONS, MEMBERSHIP_ROLES } from '@/lib/people';
import { buttonClass, cardClass, errorClass, fieldClass } from '@/lib/styles';
import type { BoardPosition, MembershipRole } from '@/types/people';
import type { CommitteeOption } from '@/types/schedule';
import { useForm } from '@inertiajs/react';
import { Check } from 'lucide-react';
import { useState, type FormEvent, type ReactNode } from 'react';

/**
 * Agregar a la lista del comité a alguien que ya está en el padrón (RF-085): se busca por el
 * nombre —el buscador solo trae nombre y edad—, se escoge el comité o sub-comité, y su función.
 * Si la persona es nueva, la registra el pastor.
 */
export default function AddToList({ committees, onDone }: { committees: CommitteeOption[]; onDone: () => void }) {
    const [query, setQuery] = useState('');
    const { results, searching } = useMemberPicker(query);
    const [committee, setCommittee] = useState<number | ''>(committees.length === 1 ? committees[0].id : '');
    const form = useForm<{ member_id: number | ''; role: MembershipRole; board_position: BoardPosition | '' }>({ member_id: '', role: 'member', board_position: '' });

    function submit(event: FormEvent) {
        event.preventDefault();
        if (!committee) return;
        form.post(`/committees/${committee}/members`, { preserveScroll: true, onSuccess: onDone });
    }

    return (
        <form onSubmit={submit} className={`mb-6 flex flex-col gap-4 p-4 ${cardClass}`}>
            <SearchBox label="Buscar en el padrón" placeholder="Nombre de la persona" value={query} onChange={setQuery} />
            {searching && <p className="text-lg text-tinta-suave">Buscando…</p>}
            {query.trim().length >= 2 && !searching && results.length === 0 && (
                <p className="text-lg text-tinta-suave">No aparece nadie con ese nombre. Si es nueva en la congregación, pídale al pastor que la registre.</p>
            )}
            <PickerResults results={results} chosen={form.data.member_id} onChoose={(id) => form.setData('member_id', id)} />
            {form.errors.member_id && <p className={errorClass}>{form.errors.member_id}</p>}

            {committees.length > 1 && (
                <Select id="list-committee" label="A la lista de" value={committee} onChange={(value) => setCommittee(value ? Number(value) : '')}>
                    <option value="">Escoja el comité</option>
                    {committees.map((option) => (
                        <option key={option.id} value={option.id}>
                            {option.name}
                        </option>
                    ))}
                </Select>
            )}
            <Select id="list-role" label="Qué hace en el comité" value={form.data.role} onChange={(value) => form.setData('role', value as MembershipRole)}>
                {MEMBERSHIP_ROLES.map((role) => (
                    <option key={role.value} value={role.value}>
                        {role.label}
                    </option>
                ))}
            </Select>
            {form.data.role === 'board' && (
                <Select id="list-position" label="Su cargo en la directiva" value={form.data.board_position} onChange={(value) => form.setData('board_position', value as BoardPosition)} error={form.errors.board_position}>
                    <option value="">Escoja el cargo</option>
                    {BOARD_POSITIONS.map((position) => (
                        <option key={position.value} value={position.value}>
                            {position.label}
                        </option>
                    ))}
                </Select>
            )}

            <button type="submit" disabled={form.processing || !form.data.member_id || !committee} className={buttonClass.primary}>
                Agregar a la lista
            </button>
        </form>
    );
}

function PickerResults({ results, chosen, onChoose }: { results: PickerResult[]; chosen: number | ''; onChoose: (id: number) => void }) {
    if (results.length === 0) return null;

    return (
        <ul role="radiogroup" aria-label="Personas encontradas" className="flex flex-col gap-1">
            {results.map((person) => (
                <li key={person.id}>
                    <button
                        type="button"
                        role="radio"
                        aria-checked={chosen === person.id}
                        onClick={() => onChoose(person.id)}
                        className={`flex min-h-12 w-full items-center gap-3 rounded-xl px-3 text-left text-lg ${chosen === person.id ? 'bg-ipuc text-white' : 'hover:bg-ipuc/5'}`}
                    >
                        <span className="min-w-0 flex-1 font-semibold break-words">{person.name}</span>
                        <span>{ageText(person.age)}</span>
                        {chosen === person.id && <Check aria-hidden className="size-6" />}
                    </button>
                </li>
            ))}
        </ul>
    );
}

interface SelectProps {
    id: string;
    label: string;
    value: string | number;
    onChange: (value: string) => void;
    error?: string;
    children: ReactNode;
}

function Select({ id, label, value, onChange, error, children }: SelectProps) {
    return (
        <div>
            <label htmlFor={id} className="block text-lg font-semibold">
                {label}
            </label>
            <select id={id} value={value} onChange={(event) => onChange(event.target.value)} aria-invalid={error ? true : undefined} className={fieldClass}>
                {children}
            </select>
            {error && <p className={errorClass}>{error}</p>}
        </div>
    );
}
