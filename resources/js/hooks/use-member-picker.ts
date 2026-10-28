import { useEffect, useState } from 'react';

export interface PickerResult {
    id: number;
    name: string;
    age: number | null;
}

/**
 * El buscador del padrón (RF-085): con dos letras o más pregunta al servidor, que responde solo el
 * nombre y la edad de quien coincide. Espera a que la persona deje de escribir para no preguntar
 * con cada letra.
 */
export function useMemberPicker(query: string): { results: PickerResult[]; searching: boolean } {
    const [results, setResults] = useState<PickerResult[]>([]);
    const [searching, setSearching] = useState(false);
    const term = query.trim();

    useEffect(() => {
        if (term.length < 2) {
            setResults([]);
            return;
        }

        const controller = new AbortController();
        const timer = window.setTimeout(() => {
            setSearching(true);
            fetch(`/members/picker?q=${encodeURIComponent(term)}`, { headers: { Accept: 'application/json' }, signal: controller.signal })
                .then((response) => response.json())
                .then((data: { results: PickerResult[] }) => setResults(data.results))
                .catch(() => undefined)
                .finally(() => setSearching(false));
        }, 300);

        return () => {
            window.clearTimeout(timer);
            controller.abort();
        };
    }, [term]);

    return { results: term.length < 2 ? [] : results, searching };
}
