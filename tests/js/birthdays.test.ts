import { turnsText, whoLabel, withoutDateText } from '@/lib/birthdays';
import type { BirthdayItem } from '@/types/birthdays';
import { describe, expect, it } from 'vitest';

const miembro: BirthdayItem = { kind: 'member', id: 1, name: 'Hermana Luz', code: 'M-0012', date: '2026-11-07', turns: 45 };

describe('los cumpleaños, dichos con palabras', () => {
    it('los años que cumple, en singular y en plural', () => {
        expect(turnsText(45)).toBe('cumple 45 años');
        expect(turnsText(1)).toBe('cumple 1 año');
    });

    it('el miembro con su código; el simpatizante no tiene', () => {
        expect(whoLabel(miembro)).toBe('Miembro · M-0012');
        expect(whoLabel({ ...miembro, code: null })).toBe('Miembro');
        expect(whoLabel({ ...miembro, kind: 'sympathizer', code: null })).toBe('Simpatizante');
    });

    it('cuántas fichas no tienen fecha, en singular y en plural', () => {
        expect(withoutDateText(1)).toBe('Una ficha no tiene fecha de nacimiento y no sale en la lista. Se completa en la ficha de la persona.');
        expect(withoutDateText(12)).toBe('12 fichas no tienen fecha de nacimiento y no salen en la lista. Se completa en la ficha de cada persona.');
    });
});
