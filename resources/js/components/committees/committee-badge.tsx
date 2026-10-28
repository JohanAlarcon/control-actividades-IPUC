import { committeeInitials } from '@/lib/committees';

const SIZES = {
    md: 'size-16 text-xl',
    lg: 'size-24 text-3xl',
};

interface Props {
    name: string;
    logoUrl: string | null;
    size?: keyof typeof SIZES;
}

/** El logo del comité o, si no tiene, sus iniciales en un círculo: nunca un espacio vacío (HU-26 escenario 3). */
export default function CommitteeBadge({ name, logoUrl, size = 'lg' }: Props) {
    if (logoUrl) {
        return <img src={logoUrl} alt={`Logo de ${name}`} className={`shrink-0 rounded-full bg-white object-contain p-1 shadow-tarjeta ${SIZES[size]}`} />;
    }

    return (
        <span aria-hidden className={`flex shrink-0 items-center justify-center rounded-full bg-ipuc font-bold text-white ${SIZES[size]}`}>
            {committeeInitials(name)}
        </span>
    );
}
