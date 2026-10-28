import TextField from '@/components/text-field';
import { useRemoteSearch } from '@/hooks/use-remote-search';
import { Link } from '@inertiajs/react';
import { ChevronRight, UserCheck } from 'lucide-react';

interface Suggestion {
    id: number;
    name: string;
    phone: string | null;
}

interface Props {
    value: string;
    onChange: (name: string) => void;
    error?: string;
}

/**
 * El nombre, con los ya registrados que coinciden mientras se escribe (RF-096, HU-13 escenario 2),
 * para no registrar dos veces a la misma persona. Salen solo los del ámbito: a BIS, todos; a un
 * comité, los suyos. Cada uno lleva a su ficha.
 */
export default function NameWithSuggestions({ value, onChange, error }: Props) {
    const { results } = useRemoteSearch<Suggestion>('/sympathizers/suggest', value);

    return (
        <div>
            <TextField id="name" label="Nombre" autoComplete="off" value={value} onChange={(event) => onChange(event.target.value)} error={error} />
            {results.length > 0 && (
                <div aria-live="polite" className="mt-3 rounded-2xl bg-ipuc/5 p-3">
                    <p className="flex items-center gap-2 text-lg font-semibold">
                        <UserCheck aria-hidden className="size-6 shrink-0 text-ipuc" />
                        ¿Es alguna de estas personas? Ya están registradas.
                    </p>
                    <ul className="mt-2 flex flex-col gap-1">
                        {results.map((person) => (
                            <li key={person.id}>
                                <Link
                                    href={`/sympathizers/${person.id}`}
                                    className="flex min-h-12 items-center gap-3 rounded-xl bg-white px-3 text-lg hover:bg-ipuc/5 focus-visible:outline-2 focus-visible:outline-ipuc"
                                >
                                    <span className="min-w-0 flex-1 py-1 break-words">
                                        <span className="block font-semibold">{person.name}</span>
                                        {person.phone && <span className="block text-tinta-suave">{person.phone}</span>}
                                    </span>
                                    <ChevronRight aria-hidden className="size-6 shrink-0 text-tinta-suave" />
                                </Link>
                            </li>
                        ))}
                    </ul>
                </div>
            )}
        </div>
    );
}
