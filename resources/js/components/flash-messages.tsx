import type { Credentials, Flash } from '@/types/accounts';
import { usePage } from '@inertiajs/react';

/** Los avisos que deja el servidor después de guardar. Se muestran una sola vez. */
export default function FlashMessages() {
    const { flash } = usePage<{ flash: Flash }>().props;

    return (
        <>
            {flash.notice && (
                <p role="status" className="mb-4 rounded-lg border-l-4 border-ipuc-claro bg-white py-3 pl-4 text-lg">
                    {flash.notice}
                </p>
            )}
            {flash.temporaryPassword && <CredentialsBox {...flash.temporaryPassword} />}
        </>
    );
}

function CredentialsBox({ name, username, password }: Credentials) {
    return (
        <section aria-labelledby="credenciales" className="mb-4 rounded-lg border-2 border-ipuc-dorado bg-white p-4">
            <h2 id="credenciales" className="text-lg font-bold">
                Entréguele estos datos a {name}
            </h2>
            <dl className="mt-3 space-y-3">
                <div>
                    <dt className="text-lg text-tinta-suave">Usuario</dt>
                    <dd className="font-mono text-2xl font-bold">{username}</dd>
                </div>
                <div>
                    <dt className="text-lg text-tinta-suave">Clave temporal</dt>
                    <dd className="font-mono text-2xl font-bold">{password}</dd>
                </div>
            </dl>
            <p className="mt-3 text-lg text-tinta-suave">
                Anótelos ahora: la clave no se vuelve a mostrar. Al entrar, el sistema le pedirá escoger una clave suya.
            </p>
        </section>
    );
}
