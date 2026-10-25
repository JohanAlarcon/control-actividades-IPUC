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
        <div className="flex min-h-screen flex-col items-center justify-center bg-[#F7F6F3] px-4 py-10 text-[#1A1A18]">
            <Head title="Entrar" />

            <main className="w-full max-w-sm">
                <h1 className="text-2xl font-bold leading-tight">Comités IPUC</h1>
                <p className="mt-2 text-lg leading-relaxed text-[#5A5852]">
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
                            className="mt-2 h-12 w-full rounded-lg border border-[#C9C5BA] bg-white px-4 text-lg"
                        />
                        {errors.username && (
                            <p className="mt-2 text-base font-semibold text-[#8A2B12]">{errors.username}</p>
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
                            className="mt-2 h-12 w-full rounded-lg border border-[#C9C5BA] bg-white px-4 text-lg"
                        />
                        {errors.password && (
                            <p className="mt-2 text-base font-semibold text-[#8A2B12]">{errors.password}</p>
                        )}
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="h-12 rounded-lg bg-[#1D6A75] text-lg font-semibold text-white disabled:opacity-60"
                    >
                        Entrar
                    </button>
                </form>

                <p className="mt-6 text-base leading-relaxed text-[#5A5852]">
                    ¿Olvidó su clave? Pídale al pastor que se la restablezca.
                </p>
            </main>
        </div>
    );
}
