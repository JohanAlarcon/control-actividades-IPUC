import InfoNote from '@/components/info-note';
import { ShieldCheck } from 'lucide-react';

/**
 * El aviso de privacidad de la congregación (RF-062): qué datos se guardan, para qué se usan y a
 * quién dirigirse. Texto provisional, en lenguaje sencillo, hasta que la congregación entregue el
 * suyo (DEP-04).
 */
export default function PrivacyNotice() {
    return (
        <InfoNote icon={ShieldCheck}>
            <span className="font-bold">Los datos de la persona están cuidados.</span> La congregación guarda su nombre, su edad, su teléfono y su
            barrio solo para acompañarla y organizar el trabajo de los comités; no se comparten con nadie de afuera. Para consultarlos o corregirlos,
            hable con el pastor.
        </InfoNote>
    );
}
