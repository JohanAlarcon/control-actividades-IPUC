import InfoNote from '@/components/info-note';
import InstallOffer from '@/components/install-offer';
import PasswordField from '@/components/password-field';
import AuthLayout from '@/layouts/auth-layout';
import { bigButtonClass, errorClass, iconFieldClass } from '@/lib/styles';
import { useForm } from '@inertiajs/react';
import { CircleHelp, LoaderCircle, LogIn, UserRound } from 'lucide-react';
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
        <AuthLayout
            title="Entrar"
            heading="Le damos la bienvenida"
            intro="Entre con el usuario y la clave que le entregó el pastor."
            footer={
                <>
                    <InstallOffer />
                    <InfoNote icon={CircleHelp}>¿Olvidó su clave? Pídale al pastor que se la restablezca.</InfoNote>
                </>
            }
        >
            <form onSubmit={enviar} className="mt-6 flex flex-col gap-5">
                <div>
                    <label htmlFor="username" className="block text-lg font-semibold">
                        Su usuario
                    </label>
                    <div className="relative mt-2">
                        <UserRound aria-hidden className="pointer-events-none absolute top-1/2 left-4 size-6 -translate-y-1/2 text-tinta-suave" />
                        <input
                            id="username"
                            type="text"
                            autoComplete="username"
                            autoCapitalize="none"
                            autoCorrect="off"
                            spellCheck={false}
                            autoFocus
                            value={data.username}
                            onChange={(e) => setData('username', e.target.value)}
                            aria-invalid={errors.username ? true : undefined}
                            aria-describedby={errors.username ? 'username-error' : undefined}
                            className={`${iconFieldClass} pr-4`}
                        />
                    </div>
                    {errors.username && (
                        <p id="username-error" className={errorClass}>
                            {errors.username}
                        </p>
                    )}
                </div>

                <PasswordField
                    id="password"
                    label="Su clave"
                    autoComplete="current-password"
                    value={data.password}
                    onChange={(value) => setData('password', value)}
                    error={errors.password}
                />

                <button type="submit" disabled={processing} className={`${bigButtonClass} mt-1`}>
                    {processing ? <LoaderCircle aria-hidden className="size-6 motion-safe:animate-spin" /> : <LogIn aria-hidden className="size-6" />}
                    {processing ? 'Entrando…' : 'Entrar'}
                </button>
            </form>
        </AuthLayout>
    );
}
