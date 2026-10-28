import { canPromptInstall, installOffer, promptInstall, stillQuiet, subscribeToInstall, type InstallOffer } from '@/lib/install';
import { useEffect, useState, useSyncExternalStore } from 'react';

const KEY = 'instalar-ahora-no';

function quietSince(): string | null {
    try {
        return window.localStorage.getItem(KEY);
    } catch {
        return null;
    }
}

function opensAsApp(): boolean {
    return window.matchMedia('(display-mode: standalone)').matches || (navigator as Navigator & { standalone?: boolean }).standalone === true;
}

/** El mensaje de instalación (RF-120): qué ofrecer, instalar y «Ahora no». Hasta pintarse en el navegador no ofrece nada. */
export function useInstallOffer(): { offer: InstallOffer; install: () => void; later: () => void } {
    const canPrompt = useSyncExternalStore(subscribeToInstall, canPromptInstall, () => false);
    const [ready, setReady] = useState(false);
    const [quiet, setQuiet] = useState(false);

    useEffect(() => {
        setQuiet(stillQuiet(quietSince()));
        setReady(true);
    }, []);

    function later() {
        try {
            window.localStorage.setItem(KEY, new Date().toISOString());
        } catch {
            // Sin almacenamiento en el navegador, se calla solo en esta pantalla.
        }
        setQuiet(true);
    }

    const offer = ready && !quiet ? installOffer({ standalone: opensAsApp(), canPrompt, userAgent: navigator.userAgent }) : 'none';

    return { offer, install: () => void promptInstall(), later };
}
