import { useEffect, useState } from 'react';

/**
 * Busca en el servidor mientras la persona escribe: con dos letras o más pregunta, y espera a que
 * deje de escribir para no preguntar con cada letra. Lo usan el buscador del padrón (RF-085) y las
 * sugerencias al registrar un simpatizante (RF-096); cada uno trae solo lo que su ruta permite.
 */
export function useRemoteSearch<T>(path: string, query: string): { results: T[]; searching: boolean } {
    const [results, setResults] = useState<T[]>([]);
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
            fetch(`${path}?q=${encodeURIComponent(term)}`, { headers: { Accept: 'application/json' }, signal: controller.signal })
                .then((response) => response.json())
                .then((data: { results: T[] }) => setResults(data.results))
                .catch(() => undefined)
                .finally(() => setSearching(false));
        }, 300);

        return () => {
            window.clearTimeout(timer);
            controller.abort();
        };
    }, [path, term]);

    return { results: term.length < 2 ? [] : results, searching };
}
