import type { ReactNode } from 'react';

/** Un bloque de la pantalla con su título encima, como los grupos de ajustes de un celular. */
export default function SectionBlock({ title, children }: { title: string; children: ReactNode }) {
    return (
        <section className="mb-6">
            <h2 className="mb-3 px-1 text-xl font-bold">{title}</h2>
            {children}
        </section>
    );
}
