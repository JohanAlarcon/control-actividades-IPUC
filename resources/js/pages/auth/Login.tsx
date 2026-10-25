import IpucLogo from '@/components/ipuc-logo';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        username: '',
        password: '',
    });

    const enviar: FormEventHandler = (e) => {
        e.preventDefault();
        post('/login');
    };

    return (
        <div className="min-h-screen bg-white text-tinta">
            <Head title="Entrar" />

            <IpucLogo />

            <main className="mx-auto w-full max-w-sm px-4 py-8">
                <h1 className="text-2xl font-bold leading-tight text-ipuc">Comités IPUC</h1>
                <div className="mt-2 h-1 w-16 rounded bg-ipuc-dorado" />

                <p className="mt-4 text-lg leading-relaxed text-tinta-suave">
                    Entre con el usuario y la clave que le entregó el pastor.
                </p>

                <form onSubmit={enviar} className="mt-7 flex flex-col gap-5">
                    <div>
                        <label htmlFor="username" className="block text-lg font-semibold">
                            Su usuario
                        </label>
                        <input
                            id="username"
                            type="text"
                            autoComplete="username"
                            autoFocus
                            value={data.username}
                            onChange={(e) => setData('username', e.target.value)}
                            className="mt-2 h-12 w-full rounded-lg border border-linea bg-white px-4 text-lg focus:border-ipuc focus:ring-2 focus:ring-ipuc-claro focus:outline-none"
                        />
                        {errors.username && (
                            <p className="mt-2 text-base font-semibold text-alerta">{errors.username}</p>
                        )}
                    </div>

                    <div>
                        <label htmlFor="password" className="block text-lg font-semibold">
                            Su clave
                        </label>
                        <input
                            id="password"
                            type="password"
                            autoComplete="current-password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            className="mt-2 h-12 w-full rounded-lg border border-linea bg-white px-4 text-lg focus:border-ipuc focus:ring-2 focus:ring-ipuc-claro focus:outline-none"
                        />
                        {errors.password && (
                            <p className="mt-2 text-base font-semibold text-alerta">{errors.password}</p>
                        )}
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="h-12 rounded-lg bg-ipuc text-lg font-semibold text-white active:bg-ipuc-oscuro disabled:opacity-60"
                    >
                        Entrar
                    </button>
                </form>

                <p className="mt-6 text-base leading-relaxed text-tinta-suave">
                    ¿Olvidó su clave? Pídale al pastor que se la restablezca.
                </p>
            </main>
        </div>
    );
}
