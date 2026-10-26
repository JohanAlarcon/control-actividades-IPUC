import { ROLE_LABELS, type Role } from '@/lib/roles';
import { buttonClass } from '@/lib/styles';
import { router, usePage } from '@inertiajs/react';

type Shared = {
    auth: { roles: { id: number; role: Role; committeeName: string | null }[] };
    scope: { mustChoose: boolean };
};

/** Quien tiene dos cargos ve uno a la vez (HU-02, escenario 4): antes de ver nada, escoge con cuál. */
export default function ChooseRole() {
    const { auth, scope } = usePage<Shared>().props;

    if (!scope.mustChoose) return null;

    return (
        <section aria-labelledby="escoger-cargo" className="mb-6 rounded-lg border-2 border-ipuc-dorado p-4">
            <h2 id="escoger-cargo" className="text-lg font-bold">
                Usted tiene {auth.roles.length} cargos
            </h2>
            <p className="mt-1 text-lg">Escoja con cuál quiere trabajar ahora. Va a ver la información de ese cargo.</p>
            <div className="mt-3 flex flex-col gap-2">
                {auth.roles.map((cargo) => (
                    <button key={cargo.id} type="button" onClick={() => router.post('/role/switch', { roleId: cargo.id })} className={buttonClass.secondary}>
                        {ROLE_LABELS[cargo.role]} · {cargo.committeeName}
                    </button>
                ))}
            </div>
        </section>
    );
}
