// Affiche du HTML DÉJÀ NETTOYÉ par le serveur (HtmlSanitizer, sprint 4).
// ⚠️ Seul composant autorisé à utiliser dangerouslySetInnerHTML (étape 7, XSS).
export function RichText({
    html,
    className = '',
}: {
    html: string | null;
    className?: string;
}) {
    if (!html) {
        return null;
    }

    return (
        <div
            className={`prose-legna ${className}`}
            dangerouslySetInnerHTML={{ __html: html }}
        />
    );
}
