/**
 * Copie un texte (R-REPLI-01). Renvoie false si la copie automatique est refusée :
 * l'interface affiche alors le texte à sélectionner à la main (F-ORD-04).
 */
export async function copyText(text: string): Promise<boolean> {
    try {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(text);

            return true;
        }
    } catch {
        // on tente la méthode ancienne ci-dessous
    }

    try {
        const area = document.createElement('textarea');
        area.value = text;
        area.setAttribute('readonly', '');
        area.style.position = 'fixed';
        area.style.opacity = '0';
        document.body.appendChild(area);
        area.select();
        // Méthode ancienne (dépréciée) gardée volontairement pour les navigateurs sans presse-papiers moderne
        const ok = document.execCommand('copy');
        document.body.removeChild(area);

        return ok;
    } catch {
        return false;
    }
}
