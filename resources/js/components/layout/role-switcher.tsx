import { ROLE_LABELS } from '@/lib/roles';
import { buttonClass } from '@/lib/styles';
import type { SharedRole } from '@/types/shared';
import { router } from '@inertiajs/react';

interface Props {
    roles: SharedRole[];
    activeId: number | null;
    onCancel?: () => void;
}

export default function RoleSwitcher({ roles, activeId, onCancel }: Props) {
    return (
        <div className="mt-3 flex flex-col gap-2">
            {roles
                .filter((cargo) => cargo.id !== activeId)
                .map((cargo) => (
                    <button
                        key={cargo.id}
                        type="button"
                        onClick={() => router.post('/role/switch', { roleId: cargo.id })}
                        className={buttonClass.secondary}
                    >
                        {ROLE_LABELS[cargo.role]} · {cargo.committeeName}
                    </button>
                ))}
            {onCancel && (
                <button type="button" onClick={onCancel} className="h-12 text-lg text-tinta-suave underline underline-offset-4">
                    No cambiar
                </button>
            )}
        </div>
    );
}
