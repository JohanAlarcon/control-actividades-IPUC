import ChoiceChips from '@/components/people/choice-chips';
import TextField from '@/components/text-field';
import { ARRIVAL_SOURCES } from '@/lib/people';

interface Props {
    source: string;
    invitedBy: string;
    firstContact: string;
    today: string;
    onSource: (source: string) => void;
    onInvitedBy: (name: string) => void;
    onFirstContact: (date: string) => void;
    errors: { arrival_source?: string; invited_by_name?: string; first_contact_at?: string };
}

/** Cómo llegó a la iglesia, quién la invitó y cuándo vino por primera vez (HU-13 escenario 1). */
export default function ArrivalFields({ source, invitedBy, firstContact, today, onSource, onInvitedBy, onFirstContact, errors }: Props) {
    return (
        <>
            <ChoiceChips id="arrival_source" legend="¿Cómo llegó a la iglesia?" options={ARRIVAL_SOURCES} value={source} onChange={onSource} error={errors.arrival_source} columns={3} />
            <TextField
                id="invited_by_name"
                label="¿Quién la invitó? (si no fue usted)"
                hint="Usted queda anotado como quien la registró."
                value={invitedBy}
                onChange={(event) => onInvitedBy(event.target.value)}
                error={errors.invited_by_name}
            />
            <TextField
                id="first_contact_at"
                type="date"
                label="¿Cuándo vino por primera vez?"
                max={today}
                value={firstContact}
                onChange={(event) => onFirstContact(event.target.value)}
                error={errors.first_contact_at}
            />
        </>
    );
}
