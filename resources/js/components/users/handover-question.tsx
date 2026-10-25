import { buttonClass } from '@/lib/styles';

interface Props {
    message: string;
    sending: boolean;
    onConfirm: () => void;
    onCancel: () => void;
}

/** El cargo escogido ya tiene titular: relevarlo se confirma aparte, nunca con el mismo clic. */
export default function HandoverQuestion({ message, sending, onConfirm, onCancel }: Props) {
    return (
        <div role="alert" className="rounded-lg border-2 border-ipuc-dorado p-4">
            <p className="text-lg">{message}</p>
            <div className="mt-3 grid gap-2 sm:grid-cols-2">
                <button type="button" disabled={sending} onClick={onConfirm} className={buttonClass.primary}>
                    Sí, hacer el relevo
                </button>
                <button type="button" disabled={sending} onClick={onCancel} className={buttonClass.secondary}>
                    No, escoger otro
                </button>
            </div>
        </div>
    );
}
