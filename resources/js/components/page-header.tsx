import { Link } from '@inertiajs/react';

interface Props {
    title: string;
    backHref?: string;
    backLabel?: string;
}

export default function PageHeader({ title, backHref, backLabel = 'Volver' }: Props) {
    return (
        <header className={`bg-ipuc px-4 pb-5 text-white ${backHref ? 'pt-1' : 'pt-5'}`}>
            <div className="mx-auto max-w-2xl">
                {backHref && (
                    <Link
                        href={backHref}
                        className="-ml-2 inline-flex h-12 items-center px-2 text-lg focus-visible:outline-2 focus-visible:outline-white"
                    >
                        ← {backLabel}
                    </Link>
                )}
                <h1 className="text-2xl leading-tight font-bold">{title}</h1>
            </div>
        </header>
    );
}
