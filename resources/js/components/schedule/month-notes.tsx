import { HIGHLIGHT_INFO } from '@/lib/schedule';
import type { MonthNoteItem } from '@/types/schedule';
import { router } from '@inertiajs/react';
import { X } from 'lucide-react';
import { useState } from 'react';

interface Props {
    notes: MonthNoteItem[];
    /** El pastor las quita; los demás solo las leen. */
    canRemove: boolean;
}

/**
 * Las notas del mes junto a su nombre, como en el formato de la sede (RF-118): en mayúsculas, en
 * rojo lo de la sede y en naranja lo del distrito. En la pantalla el color se oscurece lo justo
 * para leerse bien (D-47).
 */
export default function MonthNotes({ notes, canRemove }: Props) {
    if (notes.length === 0) return null;

    return (
        <ul className="flex flex-col gap-1 px-4 pb-3">
            {notes.map((note) => (
                <Note key={note.id} note={note} canRemove={canRemove} />
            ))}
        </ul>
    );
}

/** Quitar pide una segunda confirmación en la misma línea. */
function Note({ note, canRemove }: { note: MonthNoteItem; canRemove: boolean }) {
    const [asking, setAsking] = useState(false);

    return (
        <li className={`flex flex-wrap items-center gap-2 text-lg leading-snug font-bold uppercase ${note.highlight ? HIGHLIGHT_INFO[note.highlight].text : 'text-cronograma'}`}>
            <span className="min-w-0 flex-1 break-words">{note.text}</span>
            {canRemove && !asking && (
                <button
                    type="button"
                    onClick={() => setAsking(true)}
                    aria-label={`Quitar la nota «${note.text}»`}
                    className="flex size-12 shrink-0 items-center justify-center rounded-full text-tinta-suave hover:bg-ipuc/5 focus-visible:outline-2 focus-visible:outline-ipuc"
                >
                    <X aria-hidden className="size-5" />
                </button>
            )}
            {asking && (
                <span className="flex gap-2 normal-case">
                    <button type="button" onClick={() => router.delete(`/month-notes/${note.id}`, { preserveScroll: true })} className="h-12 rounded-xl bg-alerta px-4 font-semibold text-white">
                        Sí, quitarla
                    </button>
                    <button type="button" onClick={() => setAsking(false)} className="h-12 rounded-xl px-4 font-semibold text-ipuc">
                        No
                    </button>
                </span>
            )}
        </li>
    );
}
