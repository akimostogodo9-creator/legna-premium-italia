import { ButtonLink } from '@/components/public/button-link';
import { GrowthRings } from '@/components/public/growth-rings';
import { Seo } from '@/components/public/seo';
import { useTranslations } from '@/lib/i18n';
import { home } from '@/routes';
import products from '@/routes/products';

// F-LAY-08 : 403 / 404 / 500 / 503 au design du site, en italien (sprint 3)
export default function ErrorPage({
    status,
}: {
    status: 403 | 404 | 500 | 503;
}) {
    const t = useTranslations();

    return (
        <>
            <Seo title={t(`errors.${status}_title`)} />
            <section className="relative overflow-hidden">
                <GrowthRings className="pointer-events-none absolute top-1/2 right-0 size-[30rem] translate-x-1/3 -translate-y-1/2 text-brace-500/20" />
                <div className="relative mx-auto max-w-7xl px-5 py-24 md:px-8 md:py-36">
                    <p className="font-display text-xl font-semibold text-brace-600">
                        {status}
                    </p>
                    <h1 className="mt-3 max-w-xl text-4xl md:text-6xl">
                        {t(`errors.${status}_title`)}
                    </h1>
                    <p className="mt-5 max-w-lg text-lg text-legno-700">
                        {t(`errors.${status}_text`)}
                    </p>
                    <div className="mt-10 flex flex-col gap-3 sm:flex-row">
                        <ButtonLink href={home().url}>
                            {t('actions.back_home')}
                        </ButtonLink>
                        <ButtonLink
                            href={products.index().url}
                            variant="secondary"
                        >
                            {t('actions.see_products')}
                        </ButtonLink>
                    </div>
                </div>
            </section>
        </>
    );
}
