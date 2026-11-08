import { Link } from '@inertiajs/react';
import { GrowthRings } from '@/components/public/growth-rings';
import type { CategoryData } from '@/types/catalog';

type Content = { title: string; subtitle?: string | null };

/**
 * F-HOME-04 (sprint 7) : catégories en grands visuels (photo + nom), pas en petites cartes identiques.
 * Sur téléphone : défilement horizontal au doigt.
 */
export function CategoriesShowcase({
    content,
    categories,
}: {
    content: Content;
    categories: CategoryData[];
}) {
    if (categories.length === 0) {
        return null;
    }

    return (
        <section className="bg-crema-100">
            <div className="mx-auto max-w-7xl px-5 py-20 md:px-8 md:py-24">
                <h2 className="text-[2.1rem] md:text-[3rem]">
                    {content.title}
                </h2>
                {content.subtitle && (
                    <p className="mt-4 max-w-2xl text-lg text-legno-700">
                        {content.subtitle}
                    </p>
                )}

                <ul className="-mx-5 mt-10 flex snap-x gap-4 overflow-x-auto px-5 pb-2 md:mx-0 md:grid md:grid-cols-2 md:overflow-visible md:px-0 lg:grid-cols-4">
                    {categories.map((category) => (
                        <li
                            key={category.slug}
                            className="w-72 max-w-[80vw] shrink-0 snap-start md:w-auto md:max-w-none"
                        >
                            <Link
                                href={category.url}
                                className="group relative block aspect-[3/4] overflow-hidden rounded-[var(--radius-photo)] bg-legno-900"
                            >
                                {category.image ? (
                                    <img
                                        src={category.image}
                                        alt=""
                                        loading="lazy"
                                        className="size-full object-cover transition-transform duration-500 group-hover:scale-[1.04]"
                                    />
                                ) : (
                                    <GrowthRings className="size-full text-brace-500/40" />
                                )}
                                <span className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-legno-900/85 to-transparent p-5 pt-16">
                                    <span className="font-display text-2xl font-bold text-crema-50 [font-stretch:92%]">
                                        {category.name}
                                    </span>
                                </span>
                            </Link>
                        </li>
                    ))}
                </ul>
            </div>
        </section>
    );
}
