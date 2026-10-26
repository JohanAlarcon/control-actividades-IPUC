import type { ReactNode } from 'react';

/**
 * El logo oficial de la IPUC es blanco, asi que no se ve sobre fondo claro.
 * Por eso este componente trae su propia banda azul detras, y debajo del logo lo que se le ponga.
 */
export default function IpucLogo({ children }: { children?: ReactNode }) {
    return (
        <div className="relative flex flex-col items-center overflow-hidden bg-linear-to-br from-ipuc to-ipuc-oscuro px-4 pt-10 pb-20 text-center text-white">
            <span aria-hidden className="absolute -top-20 -right-16 size-56 rounded-full bg-white/5" />
            <span aria-hidden className="absolute -bottom-24 -left-16 size-64 rounded-full bg-ipuc-claro/10" />
            <img
                src="/img/logo-ipuc.webp"
                alt="Iglesia Pentecostal Unida de Colombia"
                width={180}
                height={125}
                className="relative w-[180px]"
            />
            {children && <div className="relative">{children}</div>}
        </div>
    );
}
