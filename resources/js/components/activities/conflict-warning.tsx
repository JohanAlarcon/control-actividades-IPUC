import { buttonClass } from '@/lib/styles';
import { TriangleAlert } from 'lucide-react';

interface Props {
    message: string;
    onChangeDate: () => void;
    onInsist: () => void;
    busy: boolean;
}

/**
 * El aviso de cruce (RF-028, CL-08): ese día ya hay otra actividad. Es un aviso, no un bloqueo:
 * se puede cambiar la fecha o proponerla igual, después de coordinarlo.
 */
export default function ConflictWarning({ message, onChangeDate, onInsist, busy }: Props) {
    return (
        <div role="alert" className="rounded-2xl bg-ipuc-dorado/20 p-4 ring-1 ring-ipuc-dorado/60">
            <p className="flex items-center gap-2 text-lg font-bold">
                <TriangleAlert aria-hidden className="size-6 shrink-0" />
                Ese día se cruza con otra actividad
            </p>
            <p className="mt-2 text-lg leading-relaxed">{message}</p>
            <div className="mt-4 grid gap-3 sm:grid-cols-2">
                <button type="button" onClick={onChangeDate} className={buttonClass.secondary}>
                    Cambiar la fecha
                </button>
                <button type="button" onClick={onInsist} disabled={busy} className={buttonClass.primary}>
                    Enviarla igual
                </button>
            </div>
        </div>
    );
}
