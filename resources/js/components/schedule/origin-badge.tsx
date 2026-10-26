/**
 * De dónde salió una actividad (RF-024, HU-08 escenario 3): del plan del año, o propuesta después.
 * Se lee sin pasar el dedo por encima. Solo se muestra en lo propio: no es uno de los siete datos.
 */
export default function OriginBadge({ fromPlan }: { fromPlan: boolean }) {
    return (
        <span className={`inline-flex rounded-full px-3 py-0.5 text-lg font-semibold ${fromPlan ? 'bg-ipuc/10 text-ipuc' : 'bg-ipuc-dorado/25 text-tinta'}`}>
            {fromPlan ? 'Del plan del año' : 'Propuesta en el año'}
        </span>
    );
}
