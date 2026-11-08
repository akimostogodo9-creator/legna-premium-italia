import { about, contact, delivery, home } from '@/routes';
import products from '@/routes/products';

// CDC §5 : Home, Prodotti, Chi siamo, Consegna, Contatti (étape 3 §2.1).
// Adresses générées par Wayfinder depuis routes/web.php (sprint 3).
export const publicNavigation = [
    { labelKey: 'nav.home', href: home().url },
    { labelKey: 'nav.products', href: products.index().url },
    { labelKey: 'nav.about', href: about().url },
    { labelKey: 'nav.delivery', href: delivery().url },
    { labelKey: 'nav.contact', href: contact().url },
] as const;

/** F-LAY-03 : lien actif (« / » seulement sur la page d'accueil exacte). */
export function isActive(href: string, currentUrl: string): boolean {
    const path = currentUrl.split('?')[0];

    return href === '/'
        ? path === '/'
        : path === href || path.startsWith(`${href}/`);
}
