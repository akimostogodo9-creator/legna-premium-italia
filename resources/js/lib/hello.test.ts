// Test de mise en place (sprint 0), supprimé au sprint 6 quand les vrais tests de mailto.ts arriveront.
// Le kit utilise Vite+, qui embarque Vitest : on importe depuis 'vite-plus/test' (et non 'vitest').
import { describe, expect, it } from 'vite-plus/test';

describe('mise en place de Vitest', () => {
    it('fonctionne', () => {
        expect(encodeURIComponent('Quantità')).toBe('Quantit%C3%A0');
    });
});
