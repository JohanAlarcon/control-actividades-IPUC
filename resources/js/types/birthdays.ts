/** Un cumpleaños: de un miembro, con su código, o de un simpatizante (RF-095). */
export interface BirthdayItem {
    kind: 'member' | 'sympathizer';
    id: number;
    name: string;
    code: string | null;
    /** El día que cumple, sin hora: «2026-11-07». */
    date: string;
    turns: number;
}

/** Los de hoy y los de los próximos siete días, con el día de hoy en Colombia. */
export interface SoonBirthdays {
    today: string;
    people: BirthdayItem[];
}
