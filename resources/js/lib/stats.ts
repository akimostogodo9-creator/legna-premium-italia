import stats from '@/routes/stats';

/** Lit le jeton CSRF que Laravel place dans le cookie XSRF-TOKEN (pas de formulaire ici). */
function xsrfToken(): string {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
}

/**
 * R-STAT-03 : compte un clic Ordina / WhatsApp de façon anonyme (sprint 6).
 * « keepalive » : l'envoi continue même si la page change (ouverture de l'e-mail, de WhatsApp).
 * Une erreur ici ne doit JAMAIS empêcher le visiteur de commander.
 */
export function trackClick(
    productId: number,
    type: 'order' | 'whatsapp',
): void {
    try {
        void fetch(stats.click().url, {
            method: 'POST',
            keepalive: true,
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': xsrfToken(),
            },
            body: JSON.stringify({ product_id: productId, type }),
        }).catch(() => undefined);
    } catch {
        // ignoré volontairement
    }
}
