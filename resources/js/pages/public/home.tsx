import type { FaqItem } from '@/components/public/faq-accordion';
import { CategoriesShowcase } from '@/components/public/home/categories-showcase';
import { DeliveryTeaser } from '@/components/public/home/delivery-teaser';
import { FaqTeaser } from '@/components/public/home/faq-teaser';
import { FeaturedProducts } from '@/components/public/home/featured-products';
import { FinalCta } from '@/components/public/home/final-cta';
import type { FinalCtaContent } from '@/components/public/home/final-cta';
import { Hero } from '@/components/public/home/hero';
import type { HeroContent } from '@/components/public/home/hero';
import { Presentation } from '@/components/public/home/presentation';
import type { PresentationContent } from '@/components/public/home/presentation';
import { WhyChooseUs } from '@/components/public/home/why-choose-us';
import type { WhyContent } from '@/components/public/home/why-choose-us';
import { Seo } from '@/components/public/seo';
import products from '@/routes/products';
import type { CategoryData, ProductCard } from '@/types/catalog';

type Titled = {
    title: string;
    subtitle?: string | null;
    button_label?: string | null;
};
type Props = {
    sections: {
        hero?: HeroContent;
        presentazione?: PresentationContent;
        prodotti?: Titled;
        categorie?: Titled;
        perche_sceglierci?: WhyContent;
        consegna?: Titled;
        faq?: Titled;
        cta_finale?: FinalCtaContent;
    };
    featured: ProductCard[];
    categories: CategoryData[];
    deliverySummary: string | null;
    faqs: FaqItem[];
};

// F-HOME-01→08 (sprint 7) — ordre des sections du CDC §7 à §15 ; une section masquée par l'admin est absente.
// Le layout public (header, footer…) est ajouté par app.tsx.
export default function Home({
    sections,
    featured,
    categories,
    deliverySummary,
    faqs,
}: Props) {
    const productsVisible = Boolean(sections.prodotti && featured.length > 0);

    return (
        <>
            <Seo
                description={sections.hero?.subtitle ?? undefined}
                image={sections.hero?.image?.src}
            />
            {sections.hero && (
                <Hero
                    content={sections.hero}
                    productsAnchor={
                        productsVisible ? '#prodotti' : products.index().url
                    }
                />
            )}
            {sections.presentazione && (
                <Presentation content={sections.presentazione} />
            )}
            {sections.prodotti && (
                <FeaturedProducts
                    content={sections.prodotti}
                    products={featured}
                />
            )}
            {sections.categorie && (
                <CategoriesShowcase
                    content={sections.categorie}
                    categories={categories}
                />
            )}
            {sections.perche_sceglierci && (
                <WhyChooseUs content={sections.perche_sceglierci} />
            )}
            {sections.consegna && (
                <DeliveryTeaser
                    content={sections.consegna}
                    summary={deliverySummary}
                />
            )}
            {sections.faq && <FaqTeaser content={sections.faq} faqs={faqs} />}
            {sections.cta_finale && <FinalCta content={sections.cta_finale} />}
        </>
    );
}
