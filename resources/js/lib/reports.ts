import type { ReportRow, ReportSummary } from '@/types/reports';

/** «67 %» de un total; de un total de cero, cero, no una división rota (CL-12). */
export function percentOf(count: number, total: number): number {
    return total === 0 ? 0 : Math.round((count * 100) / total);
}

/** El largo de cada barra: la del grupo más grande ocupa todo el ancho. */
export function barWidths(rows: ReportRow[]): number[] {
    const max = Math.max(0, ...rows.map((row) => row.count));

    return rows.map((row) => percentOf(row.count, max));
}

/** Las cifras de arriba (HU-11 escenario 1), dichas con palabras. */
export function summaryItems(summary: ReportSummary): { label: string; value: string }[] {
    return [
        { label: 'Miembros', value: String(summary.total) },
        { label: 'Bautizados', value: `${summary.baptized} (${summary.baptizedPercent} %)` },
        { label: 'Casados', value: String(summary.married) },
        { label: 'Solteros', value: String(summary.single) },
        { label: 'Edad promedio', value: summary.averageAge === null ? 'Sin datos' : `${summary.averageAge} años` },
        { label: 'Sin comité', value: String(summary.withoutCommittee) },
    ];
}

/** Quienes no tienen ni fecha ni edad anotada no entran en los cortes por edad, y se dice cuántos son (RF-036). */
export function withoutAgeText(count: number): string | null {
    if (count === 0) return null;

    return count === 1
        ? 'Una persona no tiene fecha de nacimiento ni edad anotada: no entra en los cortes por edad.'
        : `${count} personas no tienen fecha de nacimiento ni edad anotada: no entran en los cortes por edad.`;
}
