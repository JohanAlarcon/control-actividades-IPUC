import { barWidths, percentOf, summaryItems, withoutAgeText } from '@/lib/reports';
import type { ReportSummary } from '@/types/reports';
import { describe, expect, it } from 'vitest';

const resumen: ReportSummary = {
    total: 120,
    baptized: 80,
    baptizedPercent: 67,
    married: 50,
    single: 40,
    averageAge: 38,
    withoutCommittee: 7,
    withoutAge: 3,
    missingConsent: 2,
    pendingProfiles: 1,
};

describe('las cifras de los reportes', () => {
    it('un porcentaje de un total de cero es cero, no una división rota (CL-12)', () => {
        expect(percentOf(80, 120)).toBe(67);
        expect(percentOf(0, 0)).toBe(0);
    });

    it('la barra del grupo más grande ocupa todo el ancho; un grupo de ceros no tiene barras', () => {
        expect(barWidths([{ label: 'A', count: 10 }, { label: 'B', count: 5 }, { label: 'C', count: 0 }])).toEqual([100, 50, 0]);
        expect(barWidths([{ label: 'A', count: 0 }])).toEqual([0]);
    });

    it('arriba: miembros, bautizados con su porcentaje, casados, solteros, edad promedio y sin comité', () => {
        expect(summaryItems(resumen).map((item) => `${item.label}: ${item.value}`)).toEqual([
            'Miembros: 120',
            'Bautizados: 80 (67 %)',
            'Casados: 50',
            'Solteros: 40',
            'Edad promedio: 38 años',
            'Sin comité: 7',
        ]);
        expect(summaryItems({ ...resumen, averageAge: null })[4].value).toBe('Sin datos');
    });

    it('dice cuántos quedan fuera de los cortes por edad, y nada si no falta nadie', () => {
        expect(withoutAgeText(0)).toBeNull();
        expect(withoutAgeText(1)).toBe('Una persona no tiene fecha de nacimiento ni edad anotada: no entra en los cortes por edad.');
        expect(withoutAgeText(3)).toBe('3 personas no tienen fecha de nacimiento ni edad anotada: no entran en los cortes por edad.');
    });
});
