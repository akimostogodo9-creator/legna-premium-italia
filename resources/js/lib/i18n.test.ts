// Sprint 2 : remplace le test de mise en place du sprint 0.
// Le kit utilise Vite+, qui embarque Vitest : on importe depuis 'vite-plus/test'.
import { describe, expect, it, vi } from 'vite-plus/test';

vi.mock('@inertiajs/react', () => ({
    usePage: () => ({
        props: {
            translations: {
                nav: { products: 'Prodotti' },
                hello: 'Ciao :name',
            },
        },
    }),
}));

import { useTranslations } from './i18n';

describe('useTranslations', () => {
    const t = useTranslations();

    it('lit une clé imbriquée', () => {
        expect(t('nav.products')).toBe('Prodotti');
    });

    it('remplace les paramètres', () => {
        expect(t('hello', { name: 'Marco' })).toBe('Ciao Marco');
    });

    it('renvoie la clé si elle est introuvable', () => {
        expect(t('nav.inconnu')).toBe('nav.inconnu');
    });

    it('renvoie la clé si elle désigne un groupe et non un texte', () => {
        expect(t('nav')).toBe('nav');
    });
});
