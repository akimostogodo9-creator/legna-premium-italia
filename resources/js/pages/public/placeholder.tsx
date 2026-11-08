import { ButtonLink } from '@/components/public/button-link';
import { Seo } from '@/components/public/seo';
import { useTranslations } from '@/lib/i18n';
import { home } from '@/routes';

// Pages publiques des sprints suivants (« in preparazione ») — sprint 3
export default function Placeholder() {
    const t = useTranslations();

    return (
        <>
            <Seo title={t('placeholder.title')} />
            <section className="mx-auto max-w-7xl px-5 py-24 md:px-8 md:py-32">
                <h1 className="max-w-xl text-4xl md:text-6xl">
                    {t('placeholder.title')}
                </h1>
                <p className="mt-5 max-w-lg text-lg text-legno-700">
                    {t('placeholder.text')}
                </p>
                <ButtonLink
                    href={home().url}
                    variant="secondary"
                    className="mt-10"
                >
                    {t('actions.back_home')}
                </ButtonLink>
            </section>
        </>
    );
}
