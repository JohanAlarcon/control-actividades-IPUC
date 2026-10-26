import { Link } from '@inertiajs/react';
import { ChevronLeft } from 'lucide-react';

interface Props {
    title: string;
    subtitle?: string;
    back?: { href: string; label: string };
}

export default function PageTitle({ title, subtitle, back }: Props) {
    return (
        <div className="mt-4">
            {back && (
                <Link
                    href={back.href}
                    className="-ml-2 inline-flex h-12 items-center gap-1 rounded-lg px-2 text-lg font-semibold text-white/90 focus-visible:outline-2 focus-visible:outline-white"
                >
                    <ChevronLeft aria-hidden className="size-5" />
                    {back.label}
                </Link>
            )}
            <h1 className="text-[1.75rem] leading-tight font-bold">{title}</h1>
            {subtitle && <p className="mt-1 text-lg text-white/85">{subtitle}</p>}
            <div aria-hidden className="mt-3 h-1 w-12 rounded-full bg-ipuc-dorado" />
        </div>
    );
}
