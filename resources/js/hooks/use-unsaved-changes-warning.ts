import { router } from '@inertiajs/react';
import { useEffect } from 'react';
import { useTranslations } from '@/lib/i18n';

/**
 * Prévient avant de quitter une page avec des modifications non enregistrées (F-ADM-00e).
 * Utilisation : useUnsavedChangesWarning(form.isDirty && !form.processing)
 */
export function useUnsavedChangesWarning(isDirty: boolean) {
    const t = useTranslations();
    const message = t('messages.unsaved_changes');

    useEffect(() => {
        if (!isDirty) {
            return;
        }

        // Navigation Inertia (clic sur un lien du menu)
        const removeListener = router.on('before', (event) => {
            const method = event.detail.visit.method;

            // Les envois du formulaire (POST/PUT/PATCH/DELETE) ne déclenchent pas l'alerte
            if (method === 'get' && !window.confirm(message)) {
                event.preventDefault();
            }
        });

        // Fermeture de l'onglet ou rechargement
        const onBeforeUnload = (event: BeforeUnloadEvent) => {
            event.preventDefault();
        };
        window.addEventListener('beforeunload', onBeforeUnload);

        return () => {
            removeListener();
            window.removeEventListener('beforeunload', onBeforeUnload);
        };
    }, [isDirty, message]);
}
