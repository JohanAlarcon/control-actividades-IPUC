/** El aviso con que el navegador ofrece instalar la aplicación: lo dan Chrome, Edge y Samsung. */
interface InstallPrompt extends Event {
    prompt: () => Promise<void>;
}

/** Qué ofrecer en la entrada: el botón de instalar, los pasos de Safari en un iPhone, o nada. */
export type InstallOffer = 'button' | 'iphone' | 'none';

/**
 * El mensaje de instalación de la entrada (RF-120). Si ya se abrió como aplicación no se ofrece; si
 * el navegador sabe instalarla, va el botón; en un iPhone o un iPad, los pasos, porque Safari no
 * tiene botón. Los demás navegadores no instalan: no se ofrece algo que no van a poder hacer.
 */
export function installOffer({ standalone, canPrompt, userAgent }: { standalone: boolean; canPrompt: boolean; userAgent: string }): InstallOffer {
    if (standalone) return 'none';
    if (canPrompt) return 'button';

    return /iphone|ipad|ipod/i.test(userAgent) ? 'iphone' : 'none';
}

const QUIET_DAYS = 14;

/** «Ahora no» calla el mensaje dos semanas en ese navegador; después se vuelve a ofrecer. */
export function stillQuiet(since: string | null, now: Date = new Date()): boolean {
    if (!since) return false;

    const elapsed = now.getTime() - new Date(since).getTime();

    return elapsed >= 0 && elapsed < QUIET_DAYS * 24 * 60 * 60 * 1000;
}

let saved: InstallPrompt | null = null;
const listeners = new Set<() => void>();
const notify = () => listeners.forEach((listener) => listener());

/** Se llama al arrancar: el navegador da su aviso una sola vez, y puede ser antes de que se pinte la entrada. */
export function listenForInstall(): void {
    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        saved = event as InstallPrompt;
        notify();
    });
    window.addEventListener('appinstalled', () => {
        saved = null;
        notify();
    });
}

export function subscribeToInstall(listener: () => void): () => void {
    listeners.add(listener);

    return () => listeners.delete(listener);
}

export function canPromptInstall(): boolean {
    return saved !== null;
}

/** Abre la ventana del navegador para instalar. El aviso sirve una sola vez. */
export async function promptInstall(): Promise<void> {
    const aviso = saved;
    saved = null;
    notify();
    await aviso?.prompt();
}
