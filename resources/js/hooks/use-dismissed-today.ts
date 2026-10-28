import { useState } from 'react';

function saved(key: string): string | null {
    try {
        return typeof window !== 'undefined' ? window.localStorage.getItem(key) : null;
    } catch {
        return null;
    }
}

/** Algo que la persona cerró hoy y vuelve a salir mañana. Se recuerda en su navegador. */
export function useDismissedToday(key: string, today: string | undefined): [boolean, () => void] {
    const [dismissedOn, setDismissedOn] = useState(() => saved(key));

    function dismiss() {
        if (!today) return;
        setDismissedOn(today);
        try {
            window.localStorage.setItem(key, today);
        } catch {
            // Sin dónde guardarlo, se cierra hasta que se vuelva a abrir la pantalla.
        }
    }

    return [today !== undefined && dismissedOn === today, dismiss];
}
