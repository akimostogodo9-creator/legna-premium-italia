import { Link } from '@inertiajs/react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { adminNavigation } from '@/config/admin-navigation';
import { useTranslations } from '@/lib/i18n';
import { dashboard } from '@/routes/admin';
import type { NavItem } from '@/types';

// Menu latéral de l'administration (sprint 2, §6.2) — liens du kit vers GitHub/documentation retirés
export function AppSidebar() {
    const t = useTranslations();

    const items: NavItem[] = adminNavigation.map((item) => ({
        title: t(item.labelKey),
        href: item.href,
        icon: item.icon,
    }));

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={items} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
