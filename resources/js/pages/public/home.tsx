import { FinalCta } from '@/components/public/home/final-cta';
import type { FinalCtaContent } from '@/components/public/home/final-cta';
import { Hero } from '@/components/public/home/hero';
import type { HeroContent } from '@/components/public/home/hero';
import { Presentation } from '@/components/public/home/presentation';
import type { PresentationContent } from '@/components/public/home/presentation';
import { WhyChooseUs } from '@/components/public/home/why-choose-us';
import type { WhyContent } from '@/components/public/home/why-choose-us';
import { Seo } from '@/components/public/seo';

type Props = {
    sections: {
        hero?: HeroContent;
        presentazione?: PresentationContent;
        perche_sceglierci?: WhyContent;
        cta_finale?: FinalCtaContent;
    };
};

// Page d'accueil v1 (sprint 3) — chaque section peut être masquée par l'admin (absente des props).
// Le layout public (header, footer…) est ajouté automatiquement par app.tsx.
export default function Home({ sections }: Props) {
    return (
        <>
            <Seo
                description={sections.hero?.subtitle ?? undefined}
                image={sections.hero?.image ?? undefined}
            />
            {sections.hero && <Hero content={sections.hero} />}
            {sections.presentazione && (
                <Presentation content={sections.presentazione} />
            )}
            {sections.perche_sceglierci && (
                <WhyChooseUs content={sections.perche_sceglierci} />
            )}
            {sections.cta_finale && <FinalCta content={sections.cta_finale} />}
        </>
    );
}
