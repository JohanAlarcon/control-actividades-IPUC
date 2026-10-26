import { Search, X } from 'lucide-react';

interface Props {
    value: string;
    onChange: (value: string) => void;
}

export default function AccountSearch({ value, onChange }: Props) {
    return (
        <div className="relative">
            <label htmlFor="buscar" className="sr-only">
                Buscar una cuenta
            </label>
            <Search aria-hidden className="pointer-events-none absolute top-1/2 left-4 size-6 -translate-y-1/2 text-tinta-suave" />
            {/* El navegador trae su propia X para borrar, muy pequeña para el dedo: se esconde y se usa la de abajo. */}
            <input
                id="buscar"
                type="search"
                value={value}
                onChange={(e) => onChange(e.target.value)}
                placeholder="Nombre, usuario o comité"
                enterKeyHint="search"
                autoComplete="off"
                className="h-14 w-full rounded-2xl border border-linea bg-white pr-12 pl-12 text-lg shadow-tarjeta placeholder:text-tinta-suave focus:border-ipuc focus:ring-2 focus:ring-ipuc-claro focus:outline-none [&::-webkit-search-cancel-button]:appearance-none"
            />
            {value && (
                <button
                    type="button"
                    onClick={() => onChange('')}
                    aria-label="Borrar la búsqueda"
                    className="absolute top-1 right-1 flex size-12 items-center justify-center rounded-xl text-tinta-suave"
                >
                    <X aria-hidden className="size-6" />
                </button>
            )}
        </div>
    );
}
