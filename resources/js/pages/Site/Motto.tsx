import InfoNote from '@/components/info-note';
import TextField from '@/components/text-field';
import MainLayout from '@/layouts/main-layout';
import { buttonClass, cardClass, errorClass, fieldClass } from '@/lib/styles';
import { useForm } from '@inertiajs/react';
import { Quote } from 'lucide-react';
import type { FormEvent } from 'react';

interface Motto {
    year: number;
    text: string;
    citation: string | null;
    recordedBy: string | null;
    current: boolean;
}

interface Props {
    /** El año en curso y el que viene: los únicos en que se registra un lema. */
    years: number[];
    mottos: Motto[];
}

/**
 * El lema de cada año (HU-25, RF-082), que el pastor registra junto con el cronograma general. El
 * del año en curso se ve en la entrada y en todas las pantallas; los anteriores quedan aquí.
 */
export default function SiteMottoPage({ years, mottos }: Props) {
    /** Si el año ya tiene lema, se trae para corregirlo en vez de escribirlo de nuevo. */
    const valuesFor = (year: number) => {
        const saved = mottos.find((motto) => motto.year === year);

        return { year, text: saved?.text ?? '', citation: saved?.citation ?? '' };
    };
    const form = useForm(valuesFor(years[0]));

    function choose(year: number) {
        form.setData(valuesFor(year));
    }

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post('/site-motto', { preserveScroll: true });
    }

    return (
        <MainLayout title="Lema del año" subtitle="El que se ve en la entrada y en todas las pantallas" back={{ href: '/dashboard', label: 'Inicio' }}>
            <div className="lg:grid lg:grid-cols-2 lg:items-start lg:gap-6">
                <form onSubmit={submit} noValidate className={`mb-6 flex flex-col gap-5 p-4 ${cardClass}`}>
                    <div>
                        <label htmlFor="year" className="block text-lg font-semibold">
                            Año
                        </label>
                        <select id="year" value={form.data.year} onChange={(event) => choose(Number(event.target.value))} className={fieldClass}>
                            {years.map((year) => (
                                <option key={year} value={year}>
                                    {year}
                                </option>
                            ))}
                        </select>
                        {form.errors.year && <p className={errorClass}>{form.errors.year}</p>}
                    </div>
                    <TextField id="text" label="Lema" hint="Por ejemplo: Unánimes, teniendo un mismo sentir en la misión." value={form.data.text} onChange={(event) => form.setData('text', event.target.value)} error={form.errors.text} />
                    <TextField id="citation" label="Cita bíblica (opcional)" hint="Por ejemplo: Filipenses 2:2." value={form.data.citation} onChange={(event) => form.setData('citation', event.target.value)} error={form.errors.citation} />
                    <button type="submit" disabled={form.processing} className={buttonClass.primary}>
                        Guardar el lema
                    </button>
                </form>

                <section aria-labelledby="lemas">
                    <h2 id="lemas" className="mb-3 px-1 text-xl font-bold">
                        Los lemas de la sede
                    </h2>
                    {mottos.length === 0 ? (
                        <InfoNote icon={Quote}>Todavía no hay lemas registrados. Mientras tanto, la entrada muestra solo el nombre de la sede.</InfoNote>
                    ) : (
                        <ul className={`divide-y divide-ipuc/5 ${cardClass}`}>
                            {mottos.map((motto) => (
                                <li key={motto.year} className="p-4 text-lg">
                                    <p className="font-bold">
                                        {motto.year}
                                        {motto.current && <span className="ml-2 rounded-full bg-ipuc/10 px-3 py-0.5 font-semibold text-ipuc">El de este año</span>}
                                    </p>
                                    <p className="break-words">«{motto.text}»</p>
                                    {motto.citation && <p className="text-tinta-suave">{motto.citation}</p>}
                                    {motto.recordedBy && <p className="text-tinta-suave">Lo registró {motto.recordedBy}</p>}
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            </div>
        </MainLayout>
    );
}
