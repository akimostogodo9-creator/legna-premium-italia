import { RichText } from '@/components/public/rich-text';
import { Seo } from '@/components/public/seo';
import { useTranslations } from '@/lib/i18n';

type Props = {
    page: {
        title: string;
        content: string;
        meta_description: string | null;
        updated_at: string | null;
    };
    company: Record<string, string>;
};

// F-PAGE-04 (sprint 8) : texte juridique lisible (colonne étroite, interlignage généreux)
export default function LegalPage({ page, company }: Props) {
    const t = useTranslations();
    const entries = Object.entries(company);

    return (
        <>
            <Seo title={page.title} description={page.meta_description} />
            <article className="mx-auto max-w-3xl px-5 py-14 md:px-8 md:py-20">
                <h1 className="text-[2.4rem] md:text-[3.2rem]">{page.title}</h1>
                {page.updated_at && (
                    <p className="mt-3 font-display text-sm text-legno-700">
                        {t('pages.updated_at', { date: page.updated_at })}
                    </p>
                )}

                {entries.length > 0 && (
                    <section className="mt-10 rounded-[var(--radius-card)] bg-crema-100 p-6">
                        <h2 className="text-xl">{t('pages.company_data')}</h2>
                        <dl className="mt-4 grid gap-3 sm:grid-cols-[11rem_1fr]">
                            {entries.map(([key, value]) => (
                                <div key={key} className="contents">
                                    <dt className="font-display font-medium text-legno-700">
                                        {t(`pages.${key}`)}
                                    </dt>
                                    <dd className="whitespace-pre-line">
                                        {value}
                                    </dd>
                                </div>
                            ))}
                        </dl>
                    </section>
                )}

                <RichText
                    html={page.content}
                    className="mt-10 text-legno-900/90 [&_h2]:mt-12"
                />
            </article>
        </>
    );
}
