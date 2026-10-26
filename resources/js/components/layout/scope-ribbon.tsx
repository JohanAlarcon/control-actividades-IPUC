import RoleSwitcher from '@/components/layout/role-switcher';
import { ROLE_LABELS } from '@/lib/roles';
import type { SharedProps } from '@/types/shared';
import { usePage } from '@inertiajs/react';
import { useState } from 'react';

/**
 * Con qué cargo está trabajando la persona. Siempre a la vista, porque cambia todo lo que ve
 * (HU-02, escenario 4). Quien tiene dos cargos y no ha escogido, escoge aquí antes de seguir.
 */
export default function ScopeRibbon() {
    const { auth, scope } = usePage<SharedProps>().props;
    const [switching, setSwitching] = useState(false);

    return (
        <div className="border-b border-linea/40 bg-ipuc/5">
            <div className="mx-auto max-w-2xl px-4 py-3">
                {scope.mustChoose ? (
                    <p className="text-lg font-semibold">Usted tiene {auth.roles.length} cargos. Escoja con cuál quiere trabajar:</p>
                ) : (
                    <div className="flex items-center justify-between gap-3">
                        <p className="text-lg">
                            <span className="block text-tinta-suave">Usted está actuando como</span>
                            <span className="font-semibold">{actingAs({ auth, scope })}</span>
                        </p>
                        {scope.canSwitch && !switching && (
                            <button
                                type="button"
                                onClick={() => setSwitching(true)}
                                className="h-12 shrink-0 rounded-lg px-2 text-lg font-semibold text-ipuc underline underline-offset-4"
                            >
                                Cambiar de cargo
                            </button>
                        )}
                    </div>
                )}

                {(scope.mustChoose || switching) && (
                    <RoleSwitcher
                        roles={auth.roles}
                        activeId={scope.activeRole?.id ?? null}
                        onCancel={scope.mustChoose ? undefined : () => setSwitching(false)}
                    />
                )}
            </div>
        </div>
    );
}

function actingAs({ auth, scope }: Pick<SharedProps, 'auth' | 'scope'>): string {
    if (auth.user?.isPastor) return 'Pastor · Toda la congregación';
    if (!scope.activeRole) return 'Todavía sin cargo asignado';

    return `${ROLE_LABELS[scope.activeRole.role]} · ${scope.activeRole.committeeName}`;
}
