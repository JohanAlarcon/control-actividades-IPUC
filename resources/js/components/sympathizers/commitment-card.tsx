import CommitmentBadge from '@/components/sympathizers/commitment-badge';
import { daysAgo } from '@/lib/people';
import { cardClass } from '@/lib/styles';
import type { Commitment, Thresholds } from '@/types/people';

/**
 * El compromiso y cómo se determinó (HU-14, escenario 3): los contactos recientes y los días desde
 * el último. Nadie lo escribe a mano; el sistema lo calcula con estas reglas (RF-043).
 */
export default function CommitmentCard({ commitment, thresholds }: { commitment: Commitment; thresholds: Thresholds }) {
    const contacts = commitment.recentContacts === 1 ? 'Un contacto' : `${commitment.recentContacts} contactos`;

    return (
        <section aria-labelledby="compromiso" className={`mb-6 p-4 ${cardClass}`}>
            <h2 id="compromiso" className="flex flex-wrap items-center gap-3 text-xl font-bold">
                Compromiso <CommitmentBadge level={commitment.level} />
            </h2>
            <p className="mt-2 text-lg">
                {contacts} en los últimos {thresholds.recentDays} días. El último, {daysAgo(commitment.daysSinceContact)}.
            </p>
            <details className="mt-3 text-lg text-tinta-suave">
                <summary className="min-h-12 cursor-pointer py-2 font-semibold text-ipuc">¿Cómo se calcula?</summary>
                <ul className="mt-1 flex flex-col gap-1">
                    <li>
                        Creciendo: {thresholds.growingContacts} contactos o más en {thresholds.recentDays} días, y menos de {thresholds.growingDays} días sin
                        contacto.
                    </li>
                    <li>
                        Estable: al menos un contacto en {thresholds.recentDays} días, y menos de {thresholds.driftingDays} días sin contacto.
                    </li>
                    <li>Alejándose: {thresholds.driftingDays} días o más sin contacto.</li>
                </ul>
            </details>
        </section>
    );
}
