import { useEffect, useRef } from 'react';
import type { OrderLink } from '@/types/catalog';
import { OrderButton } from './order-button';
import { Price } from './price';

type Props = {
    order: OrderLink | null;
    productId: number;
    whatsappUrl: string | null;
    priceLabel: string | null;
    unit: string | null;
};

/**
 * F-PROD-05 : sur téléphone, « Ordina » toujours accessible en bas de l'écran.
 * Publie sa hauteur dans --order-bar-height pour que le bouton WhatsApp flottant se place au-dessus.
 */
export function MobileOrderBar({
    order,
    productId,
    whatsappUrl,
    priceLabel,
    unit,
}: Props) {
    const bar = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const root = document.documentElement;
        const update = () => {
            const element = bar.current;
            const visible =
                element !== null &&
                getComputedStyle(element).display !== 'none';
            root.style.setProperty(
                '--order-bar-height',
                visible ? `${element.offsetHeight}px` : '0px',
            );
        };

        update();
        window.addEventListener('resize', update);

        return () => {
            window.removeEventListener('resize', update);
            root.style.removeProperty('--order-bar-height');
        };
    }, []);

    return (
        <div
            ref={bar}
            className="fixed inset-x-0 bottom-0 z-30 flex items-center gap-4 border-t border-crema-200 bg-crema-50/95 px-4 pt-3 backdrop-blur lg:hidden"
            style={{
                paddingBottom: 'calc(0.75rem + env(safe-area-inset-bottom))',
            }}
        >
            <div className="min-w-0 flex-1">
                <Price label={priceLabel} unit={unit} />
            </div>
            <OrderButton
                order={order}
                productId={productId}
                whatsappUrl={whatsappUrl}
                className="shrink-0"
            />
        </div>
    );
}
