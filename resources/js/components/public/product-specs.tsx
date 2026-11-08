import { useTranslations } from '@/lib/i18n';

// R-CAR-02 : seules les caractéristiques renseignées arrivent du serveur
export function ProductSpecs({
    specs,
}: {
    specs: { key: string; value: string }[];
}) {
    const t = useTranslations();

    if (specs.length === 0) {
        return null;
    }

    return (
        <section>
            <h2 className="text-2xl">{t('product.specs')}</h2>
            <dl className="mt-5 divide-y divide-crema-200 border-y border-crema-200">
                {specs.map((spec) => (
                    <div
                        key={spec.key}
                        className="grid gap-1 py-4 sm:grid-cols-[12rem_1fr] sm:gap-6"
                    >
                        <dt className="font-display font-medium text-legno-700">
                            {t(`product.${spec.key}`)}
                        </dt>
                        <dd className="whitespace-pre-line text-legno-900">
                            {spec.value}
                        </dd>
                    </div>
                ))}
            </dl>
        </section>
    );
}
