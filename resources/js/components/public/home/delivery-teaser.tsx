import { Truck } from 'lucide-react';
import { ButtonLink } from '@/components/public/button-link';
import { delivery } from '@/routes';

type Content = { title: string; button_label?: string | null };

// F-HOME-06 / R-LIV-04 (sprint 7) : affiché seulement si un résumé de livraison est saisi
export function DeliveryTeaser({
    content,
    summary,
}: {
    content: Content;
    summary: string | null;
}) {
    if (!summary) {
        return null;
    }

    return (
        <section className="bg-crema-50">
            <div className="mx-auto grid max-w-7xl gap-8 px-5 py-20 md:px-8 lg:grid-cols-[auto_1fr_auto] lg:items-center lg:gap-12">
                <Truck
                    className="size-12 text-bosco-700"
                    strokeWidth={1.5}
                    aria-hidden="true"
                />
                <div className="min-w-0">
                    <h2 className="text-[2rem] md:text-[2.6rem]">
                        {content.title}
                    </h2>
                    <p className="mt-3 max-w-2xl text-lg whitespace-pre-line text-legno-900/85">
                        {summary}
                    </p>
                </div>
                {content.button_label && (
                    <ButtonLink href={delivery().url} variant="secondary">
                        {content.button_label}
                    </ButtonLink>
                )}
            </div>
        </section>
    );
}
