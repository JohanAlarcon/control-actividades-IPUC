import InfoNote from '@/components/info-note';
import MainLayout from '@/layouts/main-layout';
import { turnsText, whoLabel, withoutDateText } from '@/lib/birthdays';
import { dayTitle } from '@/lib/calendar';
import { MONTHS } from '@/lib/schedule';
import { cardClass, fieldClass } from '@/lib/styles';
import type { BirthdayItem } from '@/types/birthdays';
import { router } from '@inertiajs/react';
import { Cake, Info } from 'lucide-react';

interface Props {
    month: number;
    year: number;
    people: BirthdayItem[];
    withoutDate: number;
}

/**
 * Los cumpleaños de un mes, para planear la celebración (HU-29 escenarios 3 a 5): los miembros y
 * los simpatizantes del ámbito, con su fecha y su código.
 */
export default function BirthdaysIndex({ month, year, people, withoutDate }: Props) {
    return (
        <MainLayout title="Cumpleaños" subtitle={`${MONTHS[month - 1]} de ${year}`}>
            <label htmlFor="cumpleanos-mes" className="block text-lg font-semibold">
                Mes
            </label>
            <select
                id="cumpleanos-mes"
                value={month}
                onChange={(event) => router.get('/birthdays', { month: event.target.value }, { preserveScroll: true, replace: true })}
                className={`${fieldClass} lg:w-96`}
            >
                {MONTHS.map((name, index) => (
                    <option key={name} value={index + 1}>
                        {name}
                    </option>
                ))}
            </select>

            <div className="mt-6 flex flex-col gap-6">
                {people.length === 0 ? (
                    <InfoNote icon={Cake}>Nadie de su cargo cumple años en {MONTHS[month - 1].toLowerCase()}.</InfoNote>
                ) : (
                    <ul className={`divide-y divide-ipuc/5 ${cardClass}`}>
                        {people.map((person) => (
                            <li key={`${person.kind}-${person.id}`} className="p-4 text-lg">
                                <p className="font-bold break-words">{person.name}</p>
                                <p>
                                    {dayTitle(person.date)} · {turnsText(person.turns)}
                                </p>
                                <p className="text-tinta-suave">{whoLabel(person)}</p>
                            </li>
                        ))}
                    </ul>
                )}

                {withoutDate > 0 && <InfoNote icon={Info}>{withoutDateText(withoutDate)}</InfoNote>}
            </div>
        </MainLayout>
    );
}
