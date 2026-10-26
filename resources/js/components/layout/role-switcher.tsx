import { ROLE_LABELS } from '@/lib/roles';
import type { SharedRole } from '@/types/shared';
import { router } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';

interface Props {
    roles: SharedRole[];
    activeId: number | null;
    onCancel?: () => void;
}

/** Los otros cargos de la persona, para escoger uno. Va sobre el azul del encabezado. */
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
                        className="flex min-h-12 w-full items-center justify-between gap-3 rounded-xl bg-white px-4 py-2 text-left text-lg text-tinta focus-visible:outline-2 focus-visible:outline-white"
                    >
                        <span>
                            <span className="font-semibold">{ROLE_LABELS[cargo.role]}</span> · {cargo.committeeName}
                        </span>
                        <ChevronRight aria-hidden className="size-5 shrink-0 text-ipuc" />
                    </button>
                ))}
            {onCancel && (
                <button type="button" onClick={onCancel} className="h-12 text-lg text-white/90 underline underline-offset-4">
                    No cambiar
                </button>
            )}
        </div>
    );
}
