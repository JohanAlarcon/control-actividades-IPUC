import InitialsAvatar from '@/components/initials-avatar';
import { roleLabelFor } from '@/lib/roles';
import { cardClass } from '@/lib/styles';

/** HU-03, escenario 4: el líder sabe quién revisa lo que propone, con su nombre y sus iniciales. */
export default function ReviewerCard({ name }: { name: string }) {
    return (
        <div className={`mb-4 flex items-center gap-4 p-4 ${cardClass}`}>
            <InitialsAvatar name={name} size="lg" />
            <div className="min-w-0 flex-1 break-words">
                <p className="text-lg text-tinta-suave">Lo que usted propone lo revisa</p>
                <p className="text-lg font-bold">{name}</p>
                <p className="text-lg text-tinta-suave">{roleLabelFor('president', name)} de su comité</p>
            </div>
        </div>
    );
}
