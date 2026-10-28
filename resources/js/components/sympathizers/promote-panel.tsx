import { buttonClass, cardClass } from '@/lib/styles';
import { router } from '@inertiajs/react';
import { useState } from 'react';

/**
 * Proponer el paso a miembro cuando recorrió todo el camino (HU-14 escenario 6): la solicitud le
 * llega al pastor, que es quien aprueba. Pide una segunda confirmación.
 */
export default function PromotePanel({ sympathizerId }: { sympathizerId: number }) {
    const [confirming, setConfirming] = useState(false);

    return (
        <section className={`mb-6 p-4 ${cardClass}`}>
            <p className="text-lg font-bold">Recorrió todo el camino</p>
            <p className="text-lg text-tinta-suave">Puede proponer que pase a ser miembro. La solicitud le llega al pastor, que es quien aprueba.</p>
            {confirming ? (
                <div className="mt-3 grid grid-cols-2 gap-3">
                    <button type="button" onClick={() => setConfirming(false)} className={buttonClass.secondary}>
                        Volver
                    </button>
                    <button type="button" onClick={() => router.post(`/sympathizers/${sympathizerId}/promote`, {}, { preserveScroll: true })} className={buttonClass.primary}>
                        Sí, proponerlo
                    </button>
                </div>
            ) : (
                <button type="button" onClick={() => setConfirming(true)} className={`${buttonClass.primary} mt-3`}>
                    Proponer que pase a ser miembro
                </button>
            )}
        </section>
    );
}
