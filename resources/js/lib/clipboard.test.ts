// @vitest-environment jsdom
// R-REPLI-01 — sprint 6 (Vite+ : imports depuis 'vite-plus/test')
import { afterEach, describe, expect, it, vi } from 'vite-plus/test';
import { copyText } from './clipboard';

/** jsdom 31 ne définit pas window.isSecureContext : on le crée pour le test. */
function setSecureContext(value: boolean): void {
    Object.defineProperty(window, 'isSecureContext', {
        value,
        configurable: true,
    });
}

describe('copyText', () => {
    afterEach(() => vi.restoreAllMocks());

    it('utilise le presse-papiers moderne quand il est disponible', async () => {
        const writeText = vi.fn().mockResolvedValue(undefined);
        Object.defineProperty(navigator, 'clipboard', {
            value: { writeText },
            configurable: true,
        });
        setSecureContext(true);

        expect(await copyText('ciao')).toBe(true);
        expect(writeText).toHaveBeenCalledWith('ciao');
    });

    it('renvoie false si aucune méthode ne fonctionne', async () => {
        Object.defineProperty(navigator, 'clipboard', {
            value: undefined,
            configurable: true,
        });
        setSecureContext(false);
        document.execCommand = vi.fn().mockReturnValue(false);

        expect(await copyText('ciao')).toBe(false);
    });
});
