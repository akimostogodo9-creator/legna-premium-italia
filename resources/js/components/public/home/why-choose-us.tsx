import { Award, Handshake, MessageCircle, Truck } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

type Item = { icon?: string; title: string; text?: string | null };

export type WhyContent = { title: string; items: Item[] };

const icons: Record<string, LucideIcon> = {
    quality: Award,
    trust: Handshake,
    delivery: Truck,
    support: MessageCircle,
};

/**
 * F-HOME-05 / R-CONTENU-02 : jusqu'à 4 avantages RÉELS (les blocs désactivés sont déjà retirés par le serveur).
 * Liste en lignes séparées par un filet, pas de cartes identiques : le contenu n'est pas une séquence.
 */
export function WhyChooseUs({ content }: { content: WhyContent }) {
    if (content.items.length === 0) {
        return null;
    }

    return (
        <section className="bg-crema-50">
            <div className="mx-auto grid max-w-7xl gap-12 px-5 py-20 md:px-8 md:py-28 lg:grid-cols-[0.8fr_1.2fr]">
                <h2 className="text-[2.1rem] text-legno-900 md:text-[3rem] lg:sticky lg:top-28 lg:self-start">
                    {content.title}
                </h2>

                <ul className="grid gap-x-12 sm:grid-cols-2">
                    {content.items.map((item) => {
                        const Icon = icons[item.icon ?? ''] ?? Award;

                        return (
                            <li
                                key={item.title}
                                className="border-t border-crema-200 py-8"
                            >
                                <Icon
                                    className="size-7 text-brace-600"
                                    strokeWidth={1.6}
                                    aria-hidden="true"
                                />
                                <h3 className="mt-5 text-2xl text-legno-900">
                                    {item.title}
                                </h3>
                                {item.text && (
                                    <p className="mt-3 text-legno-900/80">
                                        {item.text}
                                    </p>
                                )}
                            </li>
                        );
                    })}
                </ul>
            </div>
        </section>
    );
}
