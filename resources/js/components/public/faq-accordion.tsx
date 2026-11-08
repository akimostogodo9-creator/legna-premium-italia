import { ChevronDown } from 'lucide-react';
import { RichText } from './rich-text';

export type FaqItem = { id: number; question: string; answer: string };

/**
 * F-HOME-07 / F-PAGE-03 (sprint 7, réutilisé au sprint 8) : questions dépliables natives (<details>),
 * donc accessibles au clavier et lisibles par Google même fermées.
 */
export function FaqAccordion({ faqs }: { faqs: FaqItem[] }) {
    return (
        <div className="divide-y divide-crema-200 border-y border-crema-200">
            {faqs.map((faq) => (
                <details key={faq.id} className="group py-1">
                    <summary className="flex cursor-pointer list-none items-center justify-between gap-6 py-5 font-display text-xl font-semibold text-legno-900 [&::-webkit-details-marker]:hidden">
                        {faq.question}
                        <ChevronDown
                            className="size-6 shrink-0 text-legno-700 transition-transform duration-200 group-open:rotate-180"
                            aria-hidden="true"
                        />
                    </summary>
                    <RichText
                        html={faq.answer}
                        className="pb-6 text-legno-900/85"
                    />
                </details>
            ))}
        </div>
    );
}
