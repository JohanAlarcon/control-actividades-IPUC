import InitialsAvatar from '@/components/initials-avatar';

interface Props {
    role: string;
    name: string | null;
}

/** Una persona con su cargo. Un cargo vacío se ve como un hueco por llenar, no como un error. */
export default function PersonRow({ role, name }: Props) {
    return (
        <li className="flex items-center gap-3 px-4 py-3">
            {name ? <InitialsAvatar name={name} /> : <span aria-hidden className="size-12 shrink-0 rounded-full border-2 border-dashed border-linea/40" />}
            <div className="min-w-0 flex-1 break-words">
                <p className="text-lg text-tinta-suave">{role}</p>
                <p className={`text-lg ${name ? 'font-semibold' : 'text-tinta-suave'}`}>{name ?? 'Sin asignar'}</p>
            </div>
        </li>
    );
}
