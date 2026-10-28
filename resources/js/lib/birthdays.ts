import type { BirthdayItem } from '@/types/birthdays';

/** «cumple 45 años»; «cumple 1 año». */
export function turnsText(turns: number): string {
    return `cumple ${turns} ${turns === 1 ? 'año' : 'años'}`;
}

/** El miembro con su código; el simpatizante no tiene. */
export function whoLabel(person: BirthdayItem): string {
    if (person.kind === 'sympathizer') return 'Simpatizante';

    return person.code ? `Miembro · ${person.code}` : 'Miembro';
}

/** Quien no tiene fecha de nacimiento no sale en la lista, y se dice cuántos son (HU-29 escenario 4). */
export function withoutDateText(count: number): string {
    return count === 1
        ? 'Una ficha no tiene fecha de nacimiento y no sale en la lista. Se completa en la ficha de la persona.'
        : `${count} fichas no tienen fecha de nacimiento y no salen en la lista. Se completa en la ficha de cada persona.`;
}
