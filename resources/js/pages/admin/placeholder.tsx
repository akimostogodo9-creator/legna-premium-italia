import { Head } from '@inertiajs/react';
import { Construction } from 'lucide-react';
import { useTranslations } from '@/lib/i18n';

// Sections des sprints suivants (« bientôt disponible »)
export default function Placeholder({ section }: { section: string | null }) {
    const t = useTranslations();
    const title = section ? t(`nav.${section}`) : t('area');

    return (
        <>
            <Head title={title} />
            <div className="flex flex-1 flex-col items-center justify-center gap-3 p-8 text-center">
                <Construction className="size-10 text-muted-foreground" />
                <h1 className="text-xl font-semibold">{title}</h1>
                <p className="text-muted-foreground">
                    {t('messages.coming_soon')}
                </p>
            </div>
        </>
    );
}
