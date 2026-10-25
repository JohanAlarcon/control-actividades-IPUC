import { Head, useForm } from '@inertiajs/react';
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

    return (
        <div className="flex min-h-screen flex-col items-center justify-center bg-white px-4 py-10 text-tinta">
            <Head title="Cambiar su clave" />

            <main className="w-full max-w-sm">
                <h1 className="text-2xl font-bold leading-tight text-ipuc">
                    {forced ? 'Escoja su clave' : 'Cambiar su clave'}
                </h1>
                <div className="mt-2 h-1 w-16 rounded bg-ipuc-dorado" />

                <p className="mt-4 text-lg leading-relaxed text-tinta-suave">
                    {forced
                        ? 'Entró con una clave temporal. Escoja una clave suya antes de continuar.'
                        : 'Escriba la clave nueva dos veces.'}
                </p>

                <form onSubmit={enviar} className="mt-7 flex flex-col gap-5">
                    <div>
                        <label htmlFor="password" className="block text-lg font-semibold">
                            Clave nueva
                        </label>
                        <input
                            id="password"
                            type="password"
                            autoComplete="new-password"
                            autoFocus
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            className="mt-2 h-12 w-full rounded-lg border border-linea bg-white px-4 text-lg focus:border-ipuc focus:ring-2 focus:ring-ipuc-claro focus:outline-none"
                        />
                        <p className="mt-2 text-base text-tinta-suave">Al menos 8 letras o números.</p>
                        {errors.password && (
                            <p className="mt-2 text-base font-semibold text-alerta">{errors.password}</p>
                        )}
                    </div>

                    <div>
                        <label htmlFor="password_confirmation" className="block text-lg font-semibold">
                            Escríbala otra vez
                        </label>
                        <input
                            id="password_confirmation"
                            type="password"
                            autoComplete="new-password"
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            className="mt-2 h-12 w-full rounded-lg border border-linea bg-white px-4 text-lg focus:border-ipuc focus:ring-2 focus:ring-ipuc-claro focus:outline-none"
                        />
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="h-12 rounded-lg bg-ipuc text-lg font-semibold text-white active:bg-ipuc-oscuro disabled:opacity-60"
                    >
                        Guardar mi clave
                    </button>
                </form>
            </main>
        </div>
    );
}
