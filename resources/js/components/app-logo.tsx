import AppLogoIcon from '@/components/app-logo-icon';
import { useTranslations } from '@/lib/i18n';

// Nom du site en texte dans le menu de l'admin (sprint 2) — le vrai logo arrive au sprint 3
export default function AppLogo() {
    const t = useTranslations();

    return (
        <>
            <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                <AppLogoIcon className="size-5 fill-current text-white" />
            </div>
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span
                    className="mb-0.5 truncate leading-tight font-semibold"
                    translate="no"
                >
                    {t('app_name')}
                </span>
            </div>
        </>
    );
}
