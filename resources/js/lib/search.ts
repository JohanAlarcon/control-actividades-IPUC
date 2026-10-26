/** Para comparar sin importar tildes ni mayúsculas: «María» y «maria» son lo mismo. */
export function normalize(text: string): string {
    return text.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
}

/** Cada palabra buscada tiene que aparecer en alguna parte: «luz alabanza» encuentra a Luz Mary, de Alabanza. */
export function matchesEveryWord(text: string, query: string): boolean {
    const target = normalize(text);

    return normalize(query)
        .split(/\s+/)
        .filter(Boolean)
        .every((word) => target.includes(word));
}
