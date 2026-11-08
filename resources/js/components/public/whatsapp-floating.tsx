import { usePage } from '@inertiajs/react';
import { useTranslations } from '@/lib/i18n';
import type { PublicPageProps } from '@/types/site';
import { WhatsAppIcon } from './whatsapp-icon';

/**
 * F-LAY-05 : bouton rond en bas à droite. Absent si aucun numéro (R-WA-04).
 * Se décale au-dessus de la barre « Ordina » mobile (sprint 6) grâce à --order-bar-height.
 */
export function WhatsAppFloating() {
    const t = useTranslations();
    const { site } = usePage<PublicPageProps>().props;

    if (!site.contacts.whatsapp_url) {
        return null;
    }

    return (
        <a
            href={site.contacts.whatsapp_url}
            target="_blank"
            rel="noopener noreferrer"
            aria-label={t('actions.whatsapp')}
            style={{ bottom: 'calc(1.25rem + var(--order-bar-height, 0px))' }}
            className="fixed right-5 z-30 inline-flex size-14 items-center justify-center rounded-full bg-bosco-700 text-crema-50 shadow-[var(--shadow-float)] transition-transform duration-200 hover:scale-105 hover:bg-bosco-800"
        >
            <WhatsAppIcon className="size-7" />
        </a>
    );
}
