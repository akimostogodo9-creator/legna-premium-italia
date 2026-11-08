import type { ProductCard } from '@/types/catalog';

const styles = {
    available: 'bg-bosco-700/10 text-bosco-700',
    on_order: 'bg-brace-500/15 text-brace-600',
    out_of_stock: 'bg-legno-900/10 text-legno-900/70',
} as const;

// R-CAR-03 : Disponibile / Su ordinazione / Esaurito
export function AvailabilityBadge({
    availability,
}: {
    availability: ProductCard['availability'];
}) {
    return (
        <span
            className={`inline-flex items-center rounded-full px-2.5 py-0.5 font-display text-[0.8rem] font-medium ${styles[availability.value]}`}
        >
            {availability.label}
        </span>
    );
}
