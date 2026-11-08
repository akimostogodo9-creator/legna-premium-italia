import { Link, router } from '@inertiajs/react';
import { ExternalLink, LogOut, UserRound } from 'lucide-react';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { UserInfo } from '@/components/user-info';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { useTranslations } from '@/lib/i18n';
import { home, logout } from '@/routes';
import profile from '@/routes/admin/profile';
import type { User } from '@/types';

type Props = {
    user: User;
};

// Menu du compte en bas du menu latéral (sprint 2, §6.2 point 4) :
// « Profilo », « Vedi il sito » (nouvel onglet) et « Esci »
export function UserMenuContent({ user }: Props) {
    const t = useTranslations();
    const cleanup = useMobileNavigation();

    const handleLogout = () => {
        cleanup();
        router.flushAll();
    };

    return (
        <>
            <DropdownMenuLabel className="p-0 font-normal">
                <div
                    className="flex items-center gap-2 px-1 py-1.5 text-left text-sm"
                    translate="no"
                >
                    <UserInfo user={user} showEmail={true} />
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
                <DropdownMenuItem asChild>
                    <Link
                        className="block w-full cursor-pointer"
                        href={profile.edit()}
                        prefetch
                        onClick={cleanup}
                    >
                        <UserRound className="mr-2" />
                        {t('nav.profile')}
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                    <a
                        className="block w-full cursor-pointer"
                        href={home().url}
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <ExternalLink className="mr-2" />
                        {t('nav.view_site')}
                    </a>
                </DropdownMenuItem>
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuItem asChild>
                <Link
                    className="block w-full cursor-pointer"
                    href={logout()}
                    as="button"
                    onClick={handleLogout}
                    data-test="logout-button"
                >
                    <LogOut className="mr-2" />
                    {t('nav.logout')}
                </Link>
            </DropdownMenuItem>
        </>
    );
}
