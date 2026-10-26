import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';

interface Props {
    icon: LucideIcon;
    children: ReactNode;
    tone?: 'info' | 'gold';
}

/** Una aclaración con su icono: lo que la pantalla explica, en vez de mostrar una sección vacía. */
export default function InfoNote({ icon: Icon, children, tone = 'info' }: Props) {
    return (
        <div className={`flex gap-3 rounded-2xl p-4 ${tone === 'gold' ? 'bg-ipuc-dorado/15' : 'bg-ipuc-claro/10'}`}>
            <Icon aria-hidden className={`mt-0.5 size-6 shrink-0 ${tone === 'gold' ? 'text-tinta' : 'text-ipuc'}`} />
            <div className="text-lg leading-relaxed">{children}</div>
        </div>
    );
}
