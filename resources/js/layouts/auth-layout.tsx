import IpucLogo from '@/components/ipuc-logo';
import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';

interface Props {
    title: string;
    heading: string;
    intro: string;
    /** Lo que va arriba del título, como el enlace para volver. */
    top?: ReactNode;
    /** Lo que va debajo de la tarjeta, como la ayuda para quien olvidó su clave. */
    footer?: ReactNode;
    children: ReactNode;
}

/**
 * El molde de las pantallas para entrar y para escoger la clave, que van sin menú: arriba el logo
 * sobre el azul de la iglesia, y el formulario en una tarjeta blanca que sube encima.
 */
export default function AuthLayout({ title, heading, intro, top, footer, children }: Props) {
    return (
        <div className="min-h-screen bg-fondo text-tinta">
            <Head title={title} />

            <IpucLogo>
                <p className="mt-4 text-2xl font-bold">Comités IPUC</p>
                <p className="text-lg text-white/85">Control de actividades</p>
            </IpucLogo>

            <main className="relative -mt-14 px-4 pb-10">
                <div className="mx-auto max-w-md rounded-3xl bg-white p-6 shadow-tarjeta">
                    {top}
                    <h1 className="text-2xl leading-tight font-bold text-ipuc">{heading}</h1>
                    <div className="mt-2 h-1 w-14 rounded-full bg-ipuc-dorado" />
                    <p className="mt-3 text-lg leading-relaxed text-tinta-suave">{intro}</p>
                    {children}
                </div>

                {footer && <div className="mx-auto mt-5 max-w-md">{footer}</div>}
            </main>
        </div>
    );
}
