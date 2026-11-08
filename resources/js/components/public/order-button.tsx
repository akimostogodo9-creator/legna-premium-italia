import { usePage } from '@inertiajs/react';
import { useState } from 'react';
import { useTranslations } from '@/lib/i18n';
import { buildMailto } from '@/lib/mailto';
import { trackClick } from '@/lib/stats';
import { contact } from '@/routes';
import type { OrderLink } from '@/types/catalog';
import type { PublicPageProps } from '@/types/site';
import { ButtonLink } from './button-link';
import { OrderHelpDialog } from './order-help-dialog';

type Props = {
    order: OrderLink | null;
    productId?: number;
    whatsappUrl?: string | null;
    /** Sur fond foncé (invitation finale) : bouton crème */
    onDark?: boolean;
    className?: string;
};

/**
 * ★ F-ORD-01/02/05/06 : un vrai lien <a href="mailto:…"> (fonctionne partout, sans blocage de fenêtre).
 * Au clic : le navigateur ouvre l'e-mail ET la fenêtre d'aide apparaît (R-REPLI-01).
 * Aucune donnée n'est enregistrée (R-ORDINA-04) — seulement un clic anonyme (R-STAT-03).
 * Pas d'e-mail configuré → « Contattaci » vers la page Contatti (R-ORDINA-05).
 */
export function OrderButton({
    order,
    productId,
    whatsappUrl,
    onDark = false,
    className = '',
}: Props) {
    const t = useTranslations();
    const { site } = usePage<PublicPageProps>().props;
    const [helpOpen, setHelpOpen] = useState(false);

    if (!order) {
        return (
            <ButtonLink
                href={contact().url}
                variant={onDark ? 'light' : 'primary'}
                className={className}
            >
                {t('actions.contact_us')}
            </ButtonLink>
        );
    }

    const href = buildMailto(order.email, order.subject, order.body);
    const colors = onDark
        ? 'bg-crema-50 text-legno-900 hover:bg-crema-100'
        : 'bg-legno-700 text-crema-50 hover:bg-legno-800';

    return (
        <>
            <a
                href={href}
                onClick={() => {
                    if (productId) {
                        trackClick(productId, 'order');
                    }

                    // laisser le navigateur suivre le lien mailto, puis afficher l'aide
                    setTimeout(() => setHelpOpen(true), 50);
                }}
                className={`inline-flex min-h-11 items-center justify-center gap-2 rounded-full px-6 py-2.5 font-display text-[0.98rem] font-semibold transition-colors duration-200 ${colors} ${className}`}
            >
                {order.label}
            </a>
            {helpOpen && (
                <OrderHelpDialog
                    order={order}
                    whatsappUrl={whatsappUrl ?? site.contacts.whatsapp_url}
                    onClose={() => setHelpOpen(false)}
                />
            )}
        </>
    );
}
