import InfoNote from '@/components/info-note';
import { shortDate } from '@/lib/format';
import { buttonClass, cardClass } from '@/lib/styles';
import type { AnnualPlanInfo } from '@/types/schedule';
import { Link, router } from '@inertiajs/react';
import { CalendarCheck, CalendarRange } from 'lucide-react';
import { useState } from 'react';

/**
 * El plan del año (HU-08): quién lo aprobó y cuándo, o cuántas actividades esperan su aprobación.
 * El pastor lo aprueba completo, de una vez, con una sola confirmación.
 */
export default function PlanBanner({ plan }: { plan: AnnualPlanInfo }) {
    const [confirming, setConfirming] = useState(false);
    const [busy, setBusy] = useState(false);

    if (plan.approved) {
        return (
            <div className="mb-6">
                <InfoNote icon={CalendarCheck}>
                    El plan {plan.year} lo aprobó <strong>{plan.approvedBy}</strong>
                    {plan.approvedAt && `, el ${shortDate(plan.approvedAt)}`}.
                </InfoNote>
            </div>
        );
    }

    if (!plan.canApprove && !plan.canLoad && plan.waiting === 0) return null;

    function approve() {
        router.post(`/annual-plan/${plan.year}/approve`, {}, { preserveScroll: true, onStart: () => setBusy(true), onFinish: () => setBusy(false) });
    }

    return (
        <section className={`mb-6 p-4 ring-1 ring-ipuc-dorado/50 ${cardClass}`}>
            <p className="flex items-center gap-2 text-lg font-bold">
                <CalendarRange aria-hidden className="size-6 shrink-0 text-ipuc" />
                El plan {plan.year} todavía no está aprobado
            </p>
            {plan.waiting > 0 && (
                <p className="mt-1 text-lg text-tinta-suave">
                    {plan.waiting === 1 ? 'Hay una actividad cargada' : `Hay ${plan.waiting} actividades cargadas`} esperando la aprobación del plan.
                </p>
            )}

            {confirming ? (
                <div className="mt-4">
                    <p className="text-lg font-semibold">¿Aprobar el plan {plan.year}?</p>
                    <p className="text-lg text-tinta-suave">Todas las actividades cargadas entran al cronograma como aprobadas, con su nombre y la fecha.</p>
                    <div className="mt-3 grid grid-cols-2 gap-3">
                        <button type="button" onClick={() => setConfirming(false)} className={buttonClass.secondary}>
                            Volver
                        </button>
                        <button type="button" onClick={approve} disabled={busy} className={buttonClass.primary}>
                            Sí, aprobarlo
                        </button>
                    </div>
                </div>
            ) : (
                <div className="mt-4 flex flex-col gap-2 lg:flex-row">
                    {plan.canApprove && (
                        <button type="button" onClick={() => setConfirming(true)} className={`${buttonClass.primary} lg:w-auto lg:px-6`}>
                            Aprobar el plan {plan.year}
                        </button>
                    )}
                    {plan.canLoad && (
                        <Link href={`/activities/create?plan=${plan.year}`} className={`${buttonClass.secondary} lg:w-auto lg:px-6`}>
                            Cargar una actividad al plan
                        </Link>
                    )}
                </div>
            )}
        </section>
    );
}
