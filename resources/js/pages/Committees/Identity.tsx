import CheckField from '@/components/check-field';
import CommitteeBadge from '@/components/committees/committee-badge';
import InfoNote from '@/components/info-note';
import TextAreaField from '@/components/text-area-field';
import TextField from '@/components/text-field';
import MainLayout from '@/layouts/main-layout';
import { buttonClass, cardClass, errorClass } from '@/lib/styles';
import { useForm } from '@inertiajs/react';
import { ImageUp, Lock } from 'lucide-react';
import { useState, type FormEvent } from 'react';

interface Props {
    committee: { id: number; name: string; logoUrl: string | null; motto: string | null; objectives: string | null };
}

/**
 * El presidente registra el logo, el lema y los objetivos de su comité o de uno de sus
 * sub-comités (HU-26, RF-083). El logo se guarda fuera de lo público, como los demás archivos.
 */
export default function CommitteeIdentityPage({ committee }: Props) {
    const form = useForm({ logo: null as File | null, remove_logo: false as boolean, motto: committee.motto ?? '', objectives: committee.objectives ?? '' });
    const [chosen, setChosen] = useState<string | null>(null);
    const preview = chosen ?? (form.data.remove_logo ? null : committee.logoUrl);

    function chooseLogo(file: File | null) {
        form.setData((data) => ({ ...data, logo: file, remove_logo: false }));
        setChosen(file ? URL.createObjectURL(file) : null);
    }

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post(`/committees/${committee.id}/identity`, { forceFormData: true });
    }

    return (
        <MainLayout title={committee.name} subtitle="Logo, lema y objetivos" back={{ href: `/committees/${committee.id}`, label: committee.name }}>
            <form onSubmit={submit} noValidate className={`mb-6 flex flex-col gap-5 p-4 lg:max-w-2xl ${cardClass}`}>
                <div className="flex flex-col gap-3">
                    <div className="flex items-center gap-4">
                        <CommitteeBadge name={committee.name} logoUrl={preview} />
                        <p className="min-w-0 text-lg text-tinta-suave">Una imagen que pese menos de 2 MB.</p>
                    </div>
                    <label className="flex min-h-12 cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-ipuc bg-white px-4 text-lg font-semibold text-ipuc focus-within:outline-2 focus-within:outline-offset-2 focus-within:outline-ipuc-claro">
                        <ImageUp aria-hidden className="size-6 shrink-0" />
                        {preview ? 'Cambiar el logo' : 'Escoger el logo'}
                        <input type="file" accept="image/png,image/jpeg,image/webp" className="sr-only" onChange={(event) => chooseLogo(event.target.files?.[0] ?? null)} />
                    </label>
                    {form.errors.logo && <p className={errorClass}>{form.errors.logo}</p>}
                </div>
                {committee.logoUrl && !form.data.logo && (
                    <CheckField id="remove_logo" label="Quitar el logo y mostrar las iniciales" checked={form.data.remove_logo} onChange={(checked) => form.setData('remove_logo', checked)} />
                )}

                <TextField id="motto" label="Lema (opcional)" hint="Una frase corta que identifique al comité." value={form.data.motto} onChange={(event) => form.setData('motto', event.target.value)} error={form.errors.motto} />
                <TextAreaField id="objectives" label="Objetivos (opcional)" value={form.data.objectives} onChange={(event) => form.setData('objectives', event.target.value)} error={form.errors.objectives} />

                <button type="submit" disabled={form.processing} className={buttonClass.primary}>
                    Guardar
                </button>
            </form>

            <div className="lg:max-w-2xl">
                <InfoNote icon={Lock}>Se ven en la ficha del comité y en lo que el comité imprime. Los demás comités solo ven su nombre.</InfoNote>
            </div>
        </MainLayout>
    );
}
