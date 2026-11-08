// Sprint 2 (§5.4) : l'admin reste en thème clair, sans choix d'apparence.
// Ce petit hook est gardé car le composant de notifications du kit (ui/sonner) l'utilise.
export type ResolvedAppearance = 'light' | 'dark';
export type Appearance = ResolvedAppearance | 'system';

export function useAppearance() {
    return { appearance: 'light' as Appearance } as const;
}
