import { Link } from '@inertiajs/react';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { useTranslations } from '@/lib/i18n';
import { toUrl } from '@/lib/utils';
import type { NavItem } from '@/types';

export function NavMain({ items }: { items: NavItem[] }) {
    const t = useTranslations();
    const { isCurrentUrl } = useCurrentUrl();

    // §6.2 point 5 : un lien est actif si l'URL courante commence par son adresse
    // (/admin/utenti/nuovo → « Utenti »), sauf « Pannello » (/admin) actif seulement sur /admin.
    const isActive = (item: NavItem) =>
        isCurrentUrl(item.href, undefined, toUrl(item.href) !== '/admin');

    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarGroupLabel>{t('nav.group')}</SidebarGroupLabel>
            <SidebarMenu>
                {items.map((item) => (
                    <SidebarMenuItem key={item.title}>
                        <SidebarMenuButton
                            asChild
                            isActive={isActive(item)}
                            tooltip={{ children: item.title }}
                        >
                            <Link href={item.href} prefetch>
                                {item.icon && <item.icon />}
                                <span>{item.title}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}
