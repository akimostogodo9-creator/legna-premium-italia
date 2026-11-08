import { useTranslations } from '@/lib/i18n';

type Props = {
    label: string | null;
    unit: string | null;
    size?: 'card' | 'detail';
};

// R-PRIX-02/03 : « € 129,00 / bancale » ou « Prezzo su richiesta »
export function Price({ label, unit, size = 'card' }: Props) {
    const t = useTranslations();
    const big = size === 'detail';

    if (!label) {
        return (
            <p
                className={`font-display font-semibold text-legno-700 ${big ? 'text-2xl' : 'text-base'}`}
            >
                {t('product.price_on_request')}
            </p>
        );
    }

    return (
        <p className="font-display text-legno-900">
            <span
                className={`font-bold tabular-nums ${big ? 'text-4xl' : 'text-xl'}`}
            >
                {label}
            </span>
            {unit && (
                <span
                    className={`ml-1.5 text-legno-700 ${big ? 'text-lg' : 'text-sm'}`}
                >
                    / {unit}
                </span>
            )}
        </p>
    );
}
