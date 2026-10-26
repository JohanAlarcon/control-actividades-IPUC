import { Search } from 'lucide-react';

interface Props {
    label: string;
    placeholder: string;
    value: string;
    onChange: (value: string) => void;
}

/** Un buscador que filtra mientras se escribe. Ignora mayúsculas y tildes (lib/search.ts). */
export default function SearchBox({ label, placeholder, value, onChange }: Props) {
    return (
        <div className="relative">
            <label htmlFor="buscar" className="sr-only">
                {label}
            </label>
            <Search aria-hidden className="pointer-events-none absolute top-1/2 left-4 size-6 -translate-y-1/2 text-tinta-suave" />
            <input
                id="buscar"
                type="search"
                autoComplete="off"
                placeholder={placeholder}
                value={value}
                onChange={(event) => onChange(event.target.value)}
                className="h-14 w-full rounded-2xl border border-linea bg-white pr-4 pl-12 text-lg shadow-tarjeta focus:border-ipuc focus:ring-2 focus:ring-ipuc-claro focus:outline-none"
            />
        </div>
    );
}
