import { ButtonLink } from '@/components/public/button-link';
import { FaqAccordion } from '@/components/public/faq-accordion';
import type { FaqItem } from '@/components/public/faq-accordion';
import { faq } from '@/routes';

type Content = { title: string; button_label?: string | null };

// F-HOME-07 (sprint 7) : jusqu'à 4 questions + lien vers la page FAQ
export function FaqTeaser({
    content,
    faqs,
}: {
    content: Content;
    faqs: FaqItem[];
}) {
    if (faqs.length === 0) {
        return null;
    }

    return (
        <section className="bg-crema-100">
            <div className="mx-auto grid max-w-7xl gap-10 px-5 py-20 md:px-8 md:py-24 lg:grid-cols-[0.8fr_1.2fr]">
                <div>
                    <h2 className="text-[2.1rem] md:text-[3rem]">
                        {content.title}
                    </h2>
                    {content.button_label && (
                        <ButtonLink
                            href={faq().url}
                            variant="secondary"
                            className="mt-8"
                        >
                            {content.button_label}
                        </ButtonLink>
                    )}
                </div>
                <FaqAccordion faqs={faqs} />
            </div>
        </section>
    );
}
