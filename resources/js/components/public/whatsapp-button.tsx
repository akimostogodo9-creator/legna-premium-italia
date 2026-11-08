import { useTranslations } from '@/lib/i18n';
import { trackClick } from '@/lib/stats';
import { WhatsAppIcon } from './whatsapp-icon';

type Props = { url: string | null; productId?: number; className?: string };

// F-WA-01→04 : toujours secondaire par rapport à « Ordina » (R-WA-05) ; absent sans numéro (R-WA-04)
export function WhatsAppButton({ url, productId, className = '' }: Props) {
    const t = useTranslations();

    if (!url) {
        return null;
    }

    return (
        <a
            href={url}
            target="_blank"
            rel="noopener noreferrer"
            onClick={() => {
                if (productId) {
                    trackClick(productId, 'whatsapp');
                }
            }}
            className={`inline-flex min-h-11 items-center justify-center gap-2 rounded-full border border-bosco-700/40 px-5 font-display font-semibold text-bosco-700 transition-colors hover:bg-bosco-700 hover:text-crema-50 ${className}`}
        >
            <WhatsAppIcon /> {t('actions.whatsapp')}
        </a>
    );
}
