/** Cuántas tarjetas van en cada página de una lista larga: en el escritorio, diez filas de dos. */
export const PAGE_SIZE = 20;

/**
 * La página que se mira de una lista larga, y cuántas hay (RF-122). Se pagina después de buscar y
 * filtrar, así que la búsqueda recorre toda la lista. Una página que ya no existe —porque la
 * búsqueda dejó menos resultados— se corrige a la última.
 */
export function pageOf<T>(items: T[], page: number, size = PAGE_SIZE): { items: T[]; page: number; pages: number } {
    const pages = Math.max(1, Math.ceil(items.length / size));
    const current = Math.min(Math.max(1, page), pages);

    return { items: items.slice((current - 1) * size, current * size), page: current, pages };
}
