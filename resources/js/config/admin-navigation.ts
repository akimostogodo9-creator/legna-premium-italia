import type { InertiaLinkProps } from '@inertiajs/react';
import {
    FileText,
    FolderTree,
    HelpCircle,
    LayoutDashboard,
    Mail,
    Package,
    Scale,
    Settings,
    Users,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { dashboard } from '@/routes/admin';
import categories from '@/routes/admin/categories';
import content from '@/routes/admin/content';
import faq from '@/routes/admin/faq';
import legal from '@/routes/admin/legal';
import messages from '@/routes/admin/messages';
import products from '@/routes/admin/products';
import settings from '@/routes/admin/settings';
import users from '@/routes/admin/users';

export type AdminNavItem = {
    /** Clé de traduction dans lang/it/admin.php (nav.*) */
    labelKey: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon: LucideIcon;
};

// Menu de l'administration (étape 3 §2.2) — URLs en italien, générées par Wayfinder
// depuis routes/admin.php (une route renommée côté Laravel casse la compilation ici).
export const adminNavigation: AdminNavItem[] = [
    { labelKey: 'nav.dashboard', href: dashboard(), icon: LayoutDashboard },
    { labelKey: 'nav.products', href: products.index(), icon: Package },
    { labelKey: 'nav.categories', href: categories.index(), icon: FolderTree },
    { labelKey: 'nav.content', href: content.index(), icon: FileText },
    { labelKey: 'nav.faq', href: faq.index(), icon: HelpCircle },
    { labelKey: 'nav.legal', href: legal.index(), icon: Scale },
    { labelKey: 'nav.settings', href: settings.index(), icon: Settings },
    { labelKey: 'nav.messages', href: messages.index(), icon: Mail },
    { labelKey: 'nav.users', href: users.index(), icon: Users },
];
