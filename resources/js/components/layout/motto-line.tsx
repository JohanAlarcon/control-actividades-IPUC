import type { SharedProps } from '@/types/shared';
import { usePage } from '@inertiajs/react';

/**
 * El lema del año, siempre a la vista bajo la barra (RF-082, HU-25 escenario 2). En el celular cabe
 * en un renglón y lo que no alcanza se corta con puntos suspensivos; el texto completo queda para
 * los lectores de pantalla. Sin lema, no ocupa nada (CL-40).
 */
export default function MottoLine() {
    const { site } = usePage<Partial<SharedProps>>().props;
    const motto = site?.motto;

    if (!motto) return null;

    return (
        <p className="mb-3 truncate text-lg text-white/85" title={`«${motto.text}»${motto.citation ? ` ${motto.citation}` : ''}`}>
            <span className="font-semibold text-ipuc-dorado">Lema {motto.year}:</span> «{motto.text}»{motto.citation && ` · ${motto.citation}`}
        </p>
    );
}
