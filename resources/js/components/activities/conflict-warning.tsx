import { buttonClass } from '@/lib/styles';
import { CalendarX2 } from 'lucide-react';

/**
 * El cruce (RF-028, D-37): ese horario ya lo tiene otra actividad del cronograma general, y así no
 * se puede guardar. El mensaje dice cuál es y de qué comité, para que se pongan de acuerdo.
 */
export default function ConflictWarning({ message, onChangeTime }: { message: string; onChangeTime: () => void }) {
    return (
        <div role="alert" className="rounded-2xl bg-alerta/10 p-4 ring-1 ring-alerta/40">
            <p className="flex items-center gap-2 text-lg font-bold text-alerta">
                <CalendarX2 aria-hidden className="size-6 shrink-0" />
                Ese horario ya está ocupado
            </p>
            <p className="mt-2 text-lg leading-relaxed">{message}</p>
            <button type="button" onClick={onChangeTime} className={`${buttonClass.secondary} mt-4`}>
                Cambiar la hora o el día
            </button>
        </div>
    );
}
