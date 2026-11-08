import { usePage } from '@inertiajs/react';

type Translations = Record<string, unknown>;
type Params = Record<string, string | number>;

/** Lit une clé « a.b.c » dans l'objet de traductions. */
function lookup(translations: Translations, key: string): string | undefined {
    const value = key.split('.').reduce<unknown>((node, part) => {
        if (node && typeof node === 'object' && part in node) {
            return (node as Translations)[part];
        }

        return undefined;
    }, translations);

    return typeof value === 'string' ? value : undefined;
}

/** Remplace « :name » par la valeur donnée (même syntaxe que Laravel). */
function interpolate(text: string, params: Params): string {
    return Object.entries(params).reduce(
        (result, [name, value]) => result.replaceAll(`:${name}`, String(value)),
        text,
    );
}

/** Traduit une clé à partir d'un objet de traductions (utilisable hors de React). */
export function translate(
    translations: Translations | undefined,
    key: string,
    params: Params = {},
): string {
    return interpolate(lookup(translations ?? {}, key) ?? key, params);
}

/**
 * Traductions de l'espace courant (étape 5 §7) — sprint 2.
 * Les clés n'incluent pas le nom du fichier :
 * t('nav.products') dans l'admin lit lang/it/admin.php → nav.products.
 * Clé introuvable → la clé elle-même s'affiche (visible tout de suite pendant le développement).
 */
export function useTranslations() {
    const { translations } = usePage().props;

    return function t(key: string, params: Params = {}): string {
        return translate(translations, key, params);
    };
}
