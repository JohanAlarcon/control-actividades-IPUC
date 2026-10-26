const SIZES = {
    sm: 'size-8 text-base',
    md: 'size-12 text-lg',
    lg: 'size-14 text-xl',
};

const TONES = {
    brand: 'bg-ipuc/10 text-ipuc',
    light: 'bg-white text-ipuc',
    muted: 'bg-linea/15 text-tinta-suave',
};

interface Props {
    name: string;
    size?: keyof typeof SIZES;
    tone?: keyof typeof TONES;
}

/** Las iniciales ayudan a encontrar a alguien de un vistazo, sin leer cada nombre. Se salta «Hermano» y «Hermana». */
export default function InitialsAvatar({ name, size = 'md', tone = 'brand' }: Props) {
    const words = name
        .replace(/^(hermano|hermana|pastor|pastora)\s+/i, '')
        .split(/\s+/)
        .filter(Boolean);
    const letters = words.length > 1 ? words[0][0] + words[words.length - 1][0] : (words[0]?.[0] ?? '');

    return (
        <span aria-hidden className={`flex shrink-0 items-center justify-center rounded-full font-bold ${SIZES[size]} ${TONES[tone]}`}>
            {letters.toUpperCase()}
        </span>
    );
}
