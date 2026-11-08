import { Link } from '@inertiajs/react';
import { useState, useSyncExternalStore } from 'react';
import { useTranslations } from '@/lib/i18n';

const STORAGE_KEY = 'lpi-cookie-notice';

function readAccepted(): boolean {
    try {
        return window.localStorage.getItem(STORAGE_KEY) === '1';
    } catch {
        return false; // navigation privée : le bandeau réapparaîtra, sans gravité
    }
}

function subscribeToStorage(callback: () => void) {
    window.addEventListener('storage', callback);

    return () => window.removeEventListener('storage', callback);
}

/**
 * F-LAY-06 / R-LEG-05 : cookies techniques uniquement → simple information, pas de choix à faire.
 * Le serveur ne connaît pas le choix mémorisé dans le navigateur : en SSR le bandeau est caché,
 * puis il apparaît dans le navigateur seulement s'il n'a pas encore été accepté.
 */
export function CookieBanner() {
    const t = useTranslations();
    const accepted = useSyncExternalStore(
        subscribeToStorage,
        readAccepted,
        () => true,
    );
    const [closed, setClosed] = useState(false);

    if (accepted || closed) {
        return null;
    }

    const accept = () => {
        try {
            window.localStorage.setItem(STORAGE_KEY, '1');
        } catch {
            // navigation privée : rien à mémoriser
        }

        setClosed(true);
    };

    return (
        <div
            role="region"
            aria-label={t('cookies.policy')}
            className="fixed inset-x-3 bottom-3 z-40 mx-auto flex max-w-2xl flex-col gap-3 rounded-[var(--radius-card)] bg-legno-900 p-4 text-crema-100 shadow-[var(--shadow-float)] sm:flex-row sm:items-center sm:gap-5 sm:p-5"
        >
            <p className="text-[0.95rem]">
                {t('cookies.text')}{' '}
                <Link
                    href="/cookie-policy"
                    className="underline underline-offset-2"
                >
                    {t('cookies.policy')}
                </Link>
            </p>
            <button
                type="button"
                onClick={accept}
                className="shrink-0 rounded-full bg-crema-100 px-5 py-2 font-display font-semibold text-legno-900 hover:bg-crema-50"
            >
                {t('cookies.ok')}
            </button>
        </div>
    );
}
