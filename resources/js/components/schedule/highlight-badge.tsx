import { HIGHLIGHT_INFO } from '@/lib/schedule';
import type { Highlight } from '@/types/schedule';

/** La marca de color dicha con palabras: en la pantalla el color nunca es la única señal (RF-116). */
export default function HighlightBadge({ highlight }: { highlight: Highlight }) {
    const { label, text } = HIGHLIGHT_INFO[highlight];

    return (
        <span className={`inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-lg font-semibold ring-1 ring-current ${text}`}>
            <span aria-hidden className="size-2.5 rounded-full bg-current" />
            {label}
        </span>
    );
}
