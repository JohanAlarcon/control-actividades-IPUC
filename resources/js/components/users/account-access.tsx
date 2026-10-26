import { buttonClass } from '@/lib/styles';
import type { EditableAccount } from '@/types/accounts';
import { router } from '@inertiajs/react';
import { useState } from 'react';

type Action = 'reset-password' | 'revoke' | 'restore';

const QUESTIONS: Record<Action, { text: string; yes: string; danger: boolean }> = {
    'reset-password': {
        text: 'Se le va a dar una clave nueva, y la que tiene deja de servir.',
        yes: 'Sí, darle una clave nueva',
        danger: false,
    },
    revoke: {
        text: 'Ya no va a poder entrar. Su nombre se conserva en todo lo que hizo.',
        yes: 'Sí, retirar el acceso',
        danger: true,
    },
    restore: {
        text: 'Va a poder entrar otra vez, con una clave nueva.',
        yes: 'Sí, habilitar la cuenta',
        danger: false,
    },
};

export default function AccountAccess({ account }: { account: EditableAccount }) {
    const [asking, setAsking] = useState<Action | null>(null);
    const [sending, setSending] = useState(false);

    // Sin conservar la posición: la clave nueva aparece arriba, y la página tiene que subir a mostrarla.
    const confirm = () => {
        if (!asking) return;

        router.post(`/users/${account.id}/${asking}`, {}, {
            onStart: () => setSending(true),
            onFinish: () => {
                setSending(false);
                setAsking(null);
            },
        });
    };

    return (
        <div>
            <p className="text-lg">{statusOf(account)}</p>

            <div className="mt-4">
                {asking ? (
                    <Question action={asking} sending={sending} onYes={confirm} onNo={() => setAsking(null)} />
                ) : (
                    <Actions account={account} onAsk={setAsking} />
                )}
            </div>
        </div>
    );
}

function statusOf(account: EditableAccount): string {
    if (!account.active) return 'No puede entrar. Su nombre se conserva en todo lo que hizo.';
    if (account.mustChangePassword) return 'Puede entrar con su clave temporal. Al hacerlo, el sistema le pedirá escoger una suya.';

    return 'Puede entrar con su usuario y su clave.';
}

function Actions({ account, onAsk }: { account: EditableAccount; onAsk: (action: Action) => void }) {
    if (!account.active) {
        return (
            <button type="button" onClick={() => onAsk('restore')} className={buttonClass.secondary}>
                Habilitar otra vez
            </button>
        );
    }

    return (
        <div className="grid gap-2 sm:grid-cols-2">
            <button type="button" onClick={() => onAsk('reset-password')} className={buttonClass.secondary}>
                Restablecer clave
            </button>
            {!account.isPastor && (
                <button type="button" onClick={() => onAsk('revoke')} className={buttonClass.secondary}>
                    Retirar acceso
                </button>
            )}
        </div>
    );
}

interface QuestionProps {
    action: Action;
    sending: boolean;
    onYes: () => void;
    onNo: () => void;
}

function Question({ action, sending, onYes, onNo }: QuestionProps) {
    const question = QUESTIONS[action];

    return (
        <div className="rounded-xl bg-ipuc-claro/10 p-4">
            <p className="text-lg">{question.text}</p>
            <div className="mt-3 grid gap-2 sm:grid-cols-2">
                <button type="button" disabled={sending} onClick={onYes} className={question.danger ? buttonClass.danger : buttonClass.primary}>
                    {question.yes}
                </button>
                <button type="button" disabled={sending} onClick={onNo} className={buttonClass.secondary} autoFocus>
                    No, dejarlo así
                </button>
            </div>
        </div>
    );
}
