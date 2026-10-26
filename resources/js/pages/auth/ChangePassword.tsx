import PasswordField from '@/components/password-field';
import AuthLayout from '@/layouts/auth-layout';
import { bigButtonClass } from '@/lib/styles';
import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, KeyRound } from 'lucide-react';
import { FormEventHandler } from 'react';

interface Props {
    forced: boolean;
}

export default function ChangePassword({ forced }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        password: '',
        password_confirmation: '',
    });

    const enviar: FormEventHandler = (e) => {
        e.preventDefault();
        post('/password/change');
    };

    const back = !forced && (
        <Link href="/dashboard" className="-ml-2 mb-3 inline-flex h-12 items-center gap-2 px-2 text-lg font-semibold text-ipuc">
            <ArrowLeft aria-hidden className="size-5" />
            Volver al inicio
        </Link>
    );

    return (
        <AuthLayout
            title="Cambiar su clave"
            heading={forced ? 'Escoja su clave' : 'Cambiar su clave'}
            intro={forced ? 'Entró con una clave temporal. Escoja una clave suya antes de continuar.' : 'Escriba la clave nueva dos veces.'}
            top={back}
        >
            <form onSubmit={enviar} className="mt-6 flex flex-col gap-5">
                <PasswordField
                    id="password"
                    label="Clave nueva"
                    autoComplete="new-password"
                    autoFocus
                    value={data.password}
                    onChange={(value) => setData('password', value)}
                    hint="Al menos 8 letras o números."
                    error={errors.password}
                />

                <PasswordField
                    id="password_confirmation"
                    label="Escríbala otra vez"
                    autoComplete="new-password"
                    value={data.password_confirmation}
                    onChange={(value) => setData('password_confirmation', value)}
                />

                <button type="submit" disabled={processing} className={`${bigButtonClass} mt-1`}>
                    <KeyRound aria-hidden className="size-6" />
                    Guardar mi clave
                </button>
            </form>
        </AuthLayout>
    );
}
