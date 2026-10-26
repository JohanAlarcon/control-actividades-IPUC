const QUINCE_MINUTOS = 15 * 60 * 1000;

/**
 * Avisa cuando hay una versión nueva del sistema (T047, RG-06). Cada pantalla trae la huella
 * de la versión con la que se sirvió; el servidor publica en /version la que está instalada
 * ahora. Si no coinciden, hubo una actualización. Se mira al volver a la aplicación y cada
 * quince minutos, porque una aplicación instalada puede quedar abierta por días.
 */
export function watchForNewVersion(loaded: string | null, onNewVersion: () => void): () => void {
    if (!loaded) return () => {};

    const check = async () => {
        try {
            const response = await fetch('/version', { cache: 'no-store', headers: { Accept: 'application/json' } });
            const { version } = response.ok ? await response.json() : { version: null };

            if (version && version !== loaded) onNewVersion();
        } catch {
            // Sin señal: se vuelve a mirar más tarde.
        }
    };

    const whenVisible = () => {
        if (document.visibilityState === 'visible') check();
    };

    document.addEventListener('visibilitychange', whenVisible);
    const timer = window.setInterval(check, QUINCE_MINUTOS);

    return () => {
        document.removeEventListener('visibilitychange', whenVisible);
        window.clearInterval(timer);
    };
}
