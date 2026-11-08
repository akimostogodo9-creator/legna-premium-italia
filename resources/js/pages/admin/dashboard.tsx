import { Head } from '@inertiajs/react';
import { LayoutDashboard } from 'lucide-react';
import { useTranslations } from '@/lib/i18n';

// F-DASH : tableau de bord provisoire (sprint 2), complété au sprint 10.
// Le menu latéral est ajouté automatiquement par app.tsx (layout de l'admin).
export default function Dashboard() {
    const t = useTranslations();

    return (
        <>
            <Head title={t('nav.dashboard')} />
            <div className="flex flex-1 flex-col gap-4 p-4 md:p-8">
                <h1 className="flex items-center gap-2 text-2xl font-semibold">
                    <LayoutDashboard className="size-6 text-muted-foreground" />
                    {t('nav.dashboard')}
                </h1>
                <p className="text-muted-foreground">
                    {t('messages.coming_soon')}
                </p>
            </div>
        </>
    );
}
