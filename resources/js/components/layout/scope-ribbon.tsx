import RoleSwitcher from '@/components/layout/role-switcher';
import { roleLabelFor } from '@/lib/roles';
import type { SharedProps } from '@/types/shared';
import { usePage } from '@inertiajs/react';
import { BadgeCheck } from 'lucide-react';
import { useState } from 'react';

/**
 * Con qué cargo está trabajando la persona. Siempre a la vista, porque cambia todo lo que ve
 * (HU-02, escenario 4). Quien tiene dos cargos y no ha escogido, escoge aquí antes de seguir.
 */
export default function ScopeRibbon() {
    const { auth, scope } = usePage<SharedProps>().props;
    const [switching, setSwitching] = useState(false);

    return (
        <div className="rounded-2xl bg-white/10 p-3 ring-1 ring-white/15">
            {scope.mustChoose ? (
                <p className="px-1 text-lg font-semibold">Usted tiene {auth.roles.length} cargos. Escoja con cuál quiere trabajar:</p>
            ) : (
                <div className="flex items-center gap-3">
                    <span aria-hidden className="flex size-10 shrink-0 items-center justify-center rounded-full bg-white/15">
                        <BadgeCheck className="size-6" />
                    </span>
                    <p className="min-w-0 flex-1 text-lg leading-snug">
                        <span className="block text-white/85">Usted está actuando como</span>
                        <span className="font-semibold">{actingAs({ auth, scope })}</span>
                    </p>
                    {scope.canSwitch && !switching && (
                        <button
                            type="button"
                            onClick={() => setSwitching(true)}
                            className="h-12 shrink-0 rounded-full bg-white px-4 text-lg font-semibold text-ipuc focus-visible:outline-2 focus-visible:outline-white"
                        >
                            Cambiar
                        </button>
                    )}
                </div>
            )}

            {(scope.mustChoose || switching) && (
                <RoleSwitcher roles={auth.roles} activeId={scope.activeRole?.id ?? null} personName={auth.user?.name} onCancel={scope.mustChoose ? undefined : () => setSwitching(false)} />
            )}
        </div>
    );
}

function actingAs({ auth, scope }: Pick<SharedProps, 'auth' | 'scope'>): string {
    if (auth.user?.isPastor) return 'Pastor · Toda la congregación';
    if (!scope.activeRole) return 'Todavía sin cargo asignado';

    return `${roleLabelFor(scope.activeRole.role, auth.user?.name)} · ${scope.activeRole.committeeName}`;
}
