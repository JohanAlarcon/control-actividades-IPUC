import { ROLE_LABELS } from '@/lib/roles';
import { buttonClass } from '@/lib/styles';
import type { Account } from '@/types/accounts';
import { Link, router } from '@inertiajs/react';
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

export default function AccountCard({ account }: { account: Account }) {
    const [asking, setAsking] = useState<Action | null>(null);
    const [sending, setSending] = useState(false);

    const confirm = () => {
        if (!asking) return;

        router.post(`/users/${account.id}/${asking}`, {}, {
            preserveScroll: true,
            onStart: () => setSending(true),
            onFinish: () => {
                setSending(false);
                setAsking(null);
            },
        });
    };

    return (
        <li className={`rounded-lg border-2 bg-white p-4 ${account.active ? 'border-linea' : 'border-dashed border-linea'}`}>
            <p className="text-lg font-bold">{account.name}</p>
            <p className="text-lg text-tinta-suave">Usuario: {account.username}</p>
            <Badges account={account} />
            <RoleList account={account} />

            <div className="mt-4">
                {asking ? (
                    <Question action={asking} sending={sending} onYes={confirm} onNo={() => setAsking(null)} />
                ) : (
                    <Actions account={account} onAsk={setAsking} />
                )}
            </div>
        </li>
    );
}

function Badges({ account }: { account: Account }) {
    const badges: string[] = [];

    if (account.isPastor) badges.push('Pastor');
    if (!account.active) badges.push('Sin acceso');
    else if (account.mustChangePassword) badges.push('Todavía no ha escogido su clave');

    if (badges.length === 0) return null;

    return (
        <ul className="mt-2 flex flex-wrap gap-2">
            {badges.map((badge) => (
                <li key={badge} className="rounded-full border border-linea px-3 py-1 text-lg">
                    {badge}
                </li>
            ))}
        </ul>
    );
}

function RoleList({ account }: { account: Account }) {
    if (account.isPastor) return null;

    if (account.roles.length === 0) {
        return <p className="mt-2 text-lg text-tinta-suave">Sin cargo asignado</p>;
    }

    return (
        <ul className="mt-2 text-lg">
            {account.roles.map((cargo) => (
                <li key={cargo.id}>
                    {ROLE_LABELS[cargo.role]} · {cargo.committeeName}
                </li>
            ))}
        </ul>
    );
}

function Actions({ account, onAsk }: { account: Account; onAsk: (action: Action) => void }) {
    return (
        <div className="grid gap-2 sm:grid-cols-3">
            <Link href={`/users/${account.id}/edit`} className={buttonClass.secondary}>
                Editar
            </Link>
            {account.active && (
                <button type="button" onClick={() => onAsk('reset-password')} className={buttonClass.secondary}>
                    Restablecer clave
                </button>
            )}
            {account.active && !account.isPastor && (
                <button type="button" onClick={() => onAsk('revoke')} className={buttonClass.secondary}>
                    Retirar acceso
                </button>
            )}
            {!account.active && (
                <button type="button" onClick={() => onAsk('restore')} className={buttonClass.secondary}>
                    Habilitar otra vez
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
        <div className="rounded-lg border-2 border-ipuc-claro p-4">
            <p className="text-lg">{question.text}</p>
            <div className="mt-3 grid gap-2 sm:grid-cols-2">
                <button
                    type="button"
                    disabled={sending}
                    onClick={onYes}
                    className={question.danger ? buttonClass.danger : buttonClass.primary}
                >
                    {question.yes}
                </button>
                <button type="button" disabled={sending} onClick={onNo} className={buttonClass.secondary} autoFocus>
                    No, dejarlo así
                </button>
            </div>
        </div>
    );
}
