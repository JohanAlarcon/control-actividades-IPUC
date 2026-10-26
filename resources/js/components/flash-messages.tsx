import InfoNote from '@/components/info-note';
import { cardClass } from '@/lib/styles';
import type { Credentials, Flash } from '@/types/accounts';
import { usePage } from '@inertiajs/react';
import { CircleCheck } from 'lucide-react';

/** Los avisos que deja el servidor después de guardar. Se muestran una sola vez. */
export default function FlashMessages() {
    const { flash } = usePage<{ flash: Flash }>().props;

    return (
        <>
            {flash.notice && (
                <div role="status" className="mb-4">
                    <InfoNote icon={CircleCheck}>{flash.notice}</InfoNote>
                </div>
            )}
            {flash.temporaryPassword && <CredentialsBox {...flash.temporaryPassword} />}
        </>
    );
}

function CredentialsBox({ name, username, password }: Credentials) {
    return (
        <section aria-labelledby="credenciales" className={`mb-4 overflow-hidden ${cardClass}`}>
            <h2 id="credenciales" className="bg-ipuc-dorado/20 px-4 py-3 text-lg font-bold">
                Entréguele estos datos a {name}
            </h2>
            <dl className="grid gap-3 p-4">
                <div>
                    <dt className="text-lg text-tinta-suave">Usuario</dt>
                    <dd className="font-mono text-2xl font-bold break-all">{username}</dd>
                </div>
                <div>
                    <dt className="text-lg text-tinta-suave">Clave temporal</dt>
                    <dd className="font-mono text-2xl font-bold break-all">{password}</dd>
                </div>
            </dl>
            <p className="px-4 pb-4 text-lg text-tinta-suave">
                Anótelos ahora: la clave no se vuelve a mostrar. Al entrar, el sistema le pedirá escoger una clave suya.
            </p>
        </section>
    );
}
