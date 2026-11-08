/** Longueur au-delà de laquelle certaines applications e-mail coupent le lien (R-ORDINA-08). */
export const MAILTO_SAFE_LENGTH = 1800;

/**
 * Construit un lien mailto correctement encodé (R-ORDINA-03) :
 * espaces, accents (UTF-8), apostrophes, « & », « ? » et retours à la ligne (CRLF).
 */
export function buildMailto(
    email: string,
    subject: string,
    body: string,
): string {
    const crlf = body.replace(/\r?\n/g, '\r\n');

    return `mailto:${email}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(crlf)}`;
}

/** Texte à copier si aucune application e-mail ne s'ouvre (R-REPLI-01). */
export function plainMessage(subject: string, body: string): string {
    return `${subject}\n\n${body}`;
}
