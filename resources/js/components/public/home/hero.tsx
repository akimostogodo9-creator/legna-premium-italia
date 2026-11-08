import { ButtonLink } from '@/components/public/button-link';
import { GrowthRings } from '@/components/public/growth-rings';
import { ResponsiveImage } from '@/components/public/responsive-image';
import { contact } from '@/routes';
import type { ImageData } from '@/types/catalog';

export type HeroContent = {
    title: string;
    subtitle?: string | null;
    primary_label: string;
    secondary_label?: string | null;
    /** Sprint 7 : objet image (src, srcset, alt) envoyé par ContentRepository */
    image?: ImageData | null;
};

/**
 * F-HOME-01 : Hero pleine largeur, photo EN ARRIÈRE-PLAN (choix d'ISI au sprint 3,
 * à la place de la photo en colonne prévue par le dossier).
 * Un voile brun garde le texte lisible. Sans photo : fond brun et cernes du bois, jamais une image inventée.
 * Le SEUL mouvement automatique du site : le fond apparaît en fondu, puis le titre ligne par ligne.
 */
export function Hero({
    content,
    productsAnchor,
}: {
    content: HeroContent;
    /** Sprint 7 (CDC §7.1) : « #prodotti » si la section produits est visible, sinon « /prodotti » */
    productsAnchor: string;
}) {
    return (
        <section className="relative isolate flex min-h-[34rem] items-end overflow-hidden bg-legno-900 text-crema-50 md:items-center lg:min-h-[min(82svh,46rem)]">
            <div className="absolute inset-0 -z-10 animate-hero-bg">
                {content.image ? (
                    <ResponsiveImage
                        image={content.image}
                        priority
                        sizes="100vw"
                        className="size-full object-cover"
                    />
                ) : (
                    <GrowthRings className="absolute top-1/2 -right-1/2 size-[140%] -translate-y-1/2 text-brace-500/35 md:-right-[15%] md:size-[85%]" />
                )}
            </div>

            {/* Voile : vers le haut sur téléphone (texte en bas), vers la droite sur ordinateur */}
            <div className="absolute inset-0 -z-10 bg-gradient-to-t from-legno-900/95 via-legno-900/60 to-legno-900/20 md:bg-gradient-to-r md:from-legno-900/90 md:via-legno-900/65 md:to-legno-900/10" />

            <div className="mx-auto w-full max-w-7xl px-5 pt-16 pb-14 md:px-8 md:py-20">
                <div className="max-w-2xl">
                    <h1 className="animate-hero-line text-[2.6rem] text-crema-50 [font-stretch:88%] [animation-delay:350ms] sm:text-[3.4rem] lg:text-[4.4rem] xl:text-[5rem]">
                        {content.title}
                    </h1>
                    {content.subtitle && (
                        <p className="mt-6 max-w-[38ch] animate-hero-line text-lg text-crema-100/85 [animation-delay:550ms] md:text-xl">
                            {content.subtitle}
                        </p>
                    )}
                    <div className="mt-10 flex animate-hero-line flex-col gap-3 [animation-delay:700ms] sm:flex-row">
                        <ButtonLink
                            href={productsAnchor}
                            external={productsAnchor.startsWith('#')}
                            variant="light"
                        >
                            {content.primary_label}
                        </ButtonLink>
                        {content.secondary_label && (
                            <ButtonLink
                                href={contact().url}
                                variant="ghost-light"
                            >
                                {content.secondary_label}
                            </ButtonLink>
                        )}
                    </div>
                </div>
            </div>
        </section>
    );
}
