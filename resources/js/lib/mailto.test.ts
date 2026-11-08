// R-ORDINA-03/08 — sprint 6 (Vite+ : imports depuis 'vite-plus/test')
import { describe, expect, it } from 'vite-plus/test';
import { buildMailto, MAILTO_SAFE_LENGTH, plainMessage } from './mailto';

describe('buildMailto', () => {
    it('encode les accents italiens en UTF-8', () => {
        const url = buildMailto(
            'ordini@example.test',
            'Richiesta di ordine',
            'Quantità desiderata:',
        );
        expect(url).toContain('Quantit%C3%A0%20desiderata%3A');
    });

    it('encode les retours à la ligne en CRLF', () => {
        expect(buildMailto('a@b.it', 'x', 'riga 1\nriga 2')).toContain(
            'riga%201%0D%0Ariga%202',
        );
    });

    it('protège « & », « ? », « = » et les apostrophes du nom du produit', () => {
        const url = buildMailto('a@b.it', "Legna d'ulivo & co? =", 'x');
        const subject = new URL(url).searchParams.get('subject');
        expect(subject).toBe("Legna d'ulivo & co? =");
        expect(url.split('&').length).toBe(2); // un seul séparateur entre subject et body
    });

    it("ne casse pas l'adresse", () => {
        expect(
            buildMailto('ordini@legna.it', 'a', 'b').startsWith(
                'mailto:ordini@legna.it?subject=',
            ),
        ).toBe(true);
    });

    it('un message type reste sous la limite de sécurité', () => {
        const body =
            'Buongiorno,\n\nVorrei ordinare il seguente prodotto:\n\nProdotto: Legna di faggio\nQuantità desiderata:\nIndirizzo di consegna:\nCAP:\nCittà:\nTelefono:\n\nVi prego di contattarmi per confermare la disponibilità, il prezzo e le modalità di consegna.\n\nCordiali saluti.';
        expect(
            buildMailto(
                'ordini@legna.it',
                'Richiesta di ordine - Legna di faggio',
                body,
            ).length,
        ).toBeLessThan(MAILTO_SAFE_LENGTH);
    });

    it('plainMessage assemble objet et corps', () => {
        expect(plainMessage('Oggetto', 'Testo')).toBe('Oggetto\n\nTesto');
    });
});
