/** Una actividad de un cronograma interno (RF-119): se dice con palabras, además del verde. */
export default function InternalBadge({ name }: { name: string | null }) {
    return (
        <span className="inline-flex rounded-full bg-interno/10 px-3 py-0.5 text-lg font-semibold break-words text-interno">
            Interno{name ? ` · ${name}` : ''}
        </span>
    );
}
