import { installOffer, stillQuiet } from '@/lib/install';
import { describe, expect, it } from 'vitest';

const android = 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 Chrome/126.0 Mobile Safari/537.36';
const iphone = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 Version/17.5 Mobile/15E148 Safari/604.1';

describe('installOffer: qué ofrecer en la entrada', () => {
    it('con el aviso del navegador, el botón de instalar', () => {
        expect(installOffer({ standalone: false, canPrompt: true, userAgent: android })).toBe('button');
    });

    it('en un iPhone, los pasos de Safari, porque no hay botón', () => {
        expect(installOffer({ standalone: false, canPrompt: false, userAgent: iphone })).toBe('iphone');
    });

    it('ya instalada, nada', () => {
        expect(installOffer({ standalone: true, canPrompt: true, userAgent: android })).toBe('none');
        expect(installOffer({ standalone: true, canPrompt: false, userAgent: iphone })).toBe('none');
    });

    it('un navegador que no sabe instalar, nada', () => {
        expect(installOffer({ standalone: false, canPrompt: false, userAgent: android })).toBe('none');
    });
});

describe('stillQuiet: «Ahora no» calla el mensaje dos semanas', () => {
    const ahora = new Date('2026-10-28T12:00:00Z');

    it('sin haberlo dicho, se ofrece', () => {
        expect(stillQuiet(null, ahora)).toBe(false);
    });

    it('hace una semana, sigue callado', () => {
        expect(stillQuiet('2026-10-21T12:00:00Z', ahora)).toBe(true);
    });

    it('hace quince días, se vuelve a ofrecer', () => {
        expect(stillQuiet('2026-10-13T12:00:00Z', ahora)).toBe(false);
    });
});
