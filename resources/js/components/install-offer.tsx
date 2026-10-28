import { useInstallOffer } from '@/hooks/use-install-offer';
import { buttonClass, cardClass } from '@/lib/styles';
import { Download, Share, Smartphone } from 'lucide-react';

/**
 * En la entrada, la invitación a instalar el sistema como una aplicación del celular (RF-120): se
 * abre con un toque desde la pantalla de inicio, sin buscarlo en el navegador.
 */
export default function InstallOffer() {
    const { offer, install, later } = useInstallOffer();

    if (offer === 'none') return null;

    return (
        <section aria-labelledby="instalar" className={`mb-5 p-4 ${cardClass}`}>
            <div className="flex gap-3">
                <Smartphone aria-hidden className="mt-0.5 size-6 shrink-0 text-ipuc" />
                <div>
                    <h2 id="instalar" className="text-lg font-bold">
                        Tenga el sistema en su celular
                    </h2>
                    <p className="text-lg leading-snug">Instálelo como una aplicación: se abre con un toque desde la pantalla de inicio.</p>
                </div>
            </div>

            {offer === 'button' ? (
                <button type="button" onClick={install} className={`${buttonClass.primary} mt-4`}>
                    <Download aria-hidden className="mr-2 size-6" />
                    Instalar la aplicación
                </button>
            ) : (
                <ol className="mt-3 list-decimal pl-7 text-lg leading-snug">
                    <li>
                        En Safari, toque el botón Compartir <Share aria-hidden className="inline size-5 align-text-bottom text-ipuc" />, el cuadrito
                        con la flecha hacia arriba.
                    </li>
                    <li>Escoja «Agregar a inicio».</li>
                </ol>
            )}

            <button type="button" onClick={later} className="mt-2 min-h-12 w-full text-lg font-semibold text-ipuc">
                Ahora no
            </button>
        </section>
    );
}
