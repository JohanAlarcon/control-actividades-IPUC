import type { ReportGroup } from '@/types/reports';

/**
 * Un grupo de cifras en tabla: exactamente lo mismo que dicen las barras (RF-037, HU-11 escenario 3).
 * Dentro de su tarjeta, que ya trae el título, el título de la columna queda solo para el lector de pantalla.
 */
export default function ReportTable({ group, titled = true }: { group: ReportGroup; titled?: boolean }) {
    return (
        <table className="mt-3 w-full text-left text-lg">
            <thead>
                <tr className="border-b-2 border-linea">
                    <th scope="col" className="py-2 font-semibold">
                        <span className={titled ? '' : 'sr-only'}>{group.title}</span>
                    </th>
                    <th scope="col" className="py-2 text-right font-semibold">
                        Cuántos
                    </th>
                </tr>
            </thead>
            <tbody>
                {group.rows.map((row) => (
                    <tr key={row.label} className="border-b border-linea/30">
                        <th scope="row" className="py-2 pr-3 font-normal break-words">
                            {row.label}
                        </th>
                        <td className="py-2 text-right font-bold">{row.count}</td>
                    </tr>
                ))}
            </tbody>
        </table>
    );
}
