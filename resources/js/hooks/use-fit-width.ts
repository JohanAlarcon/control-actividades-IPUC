import { useEffect, useState } from 'react';

function fit(width: number, gutter: number): number {
    return typeof window === 'undefined' ? 1 : Math.min(1, (window.innerWidth - gutter) / width);
}

/**
 * Cuánto hay que encoger algo de `width` píxeles para que quepa a lo ancho de la pantalla, con su
 * margen. Nunca agranda: en una pantalla ancha queda de su tamaño.
 */
export function useFitWidth(width: number, gutter = 32): number {
    const [zoom, setZoom] = useState(() => fit(width, gutter));

    useEffect(() => {
        const update = () => setZoom(fit(width, gutter));
        update();
        window.addEventListener('resize', update);

        return () => window.removeEventListener('resize', update);
    }, [width, gutter]);

    return zoom;
}
