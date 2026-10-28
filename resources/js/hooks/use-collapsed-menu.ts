import { useState } from 'react';

const KEY = 'menu-encogido';

function saved(): boolean {
    try {
        return typeof window !== 'undefined' && window.localStorage.getItem(KEY) === '1';
    } catch {
        return false;
    }
}

/** Si el menú del costado está encogido (RF-121). Se recuerda en este navegador, de una pantalla a otra. */
export function useCollapsedMenu(): [boolean, () => void] {
    const [collapsed, setCollapsed] = useState(saved);

    function toggle() {
        const next = !collapsed;
        setCollapsed(next);

        try {
            window.localStorage.setItem(KEY, next ? '1' : '0');
        } catch {
            // Sin almacenamiento en el navegador, el menú queda así solo en esta pantalla.
        }
    }

    return [collapsed, toggle];
}
