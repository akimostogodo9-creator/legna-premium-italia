import { Head, Link, router } from '@inertiajs/react';
import { Eye, EyeOff, Palette, Pencil } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/lib/i18n';
import contentRoutes from '@/routes/admin/content';
import identityRoutes from '@/routes/admin/identity';

type Section = {
    id: number;
    key: string;
    label: string;
    title: string | null;
    is_visible: boolean;
};
type Props = { pages: { key: string; sections: Section[] }[] };

// F-ACONT-01→04 (sprint 7) : toutes les sections, regroupées par page
export default function ContentIndex({ pages }: Props) {
    const t = useTranslations();

    return (
        <>
            <Head title={t('content.title')} />
            <div className="flex max-w-4xl flex-col gap-8 p-4 md:p-8">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-semibold">
                            {t('content.title')}
                        </h1>
                        <p className="mt-1 text-muted-foreground">
                            {t('content.intro')}
                        </p>
                    </div>
                    <Button variant="secondary" asChild>
                        <Link href={identityRoutes.edit()}>
                            <Palette className="size-4" /> {t('identity.title')}
                        </Link>
                    </Button>
                </div>

                {pages.map((page) => (
                    <section key={page.key}>
                        <h2 className="mb-3 text-lg font-semibold">
                            {t(`content.pages.${page.key}`)}
                        </h2>
                        <ul className="divide-y rounded-lg border">
                            {page.sections.map((s) => (
                                <li
                                    key={s.id}
                                    className="flex items-center gap-3 p-3"
                                >
                                    <div className="min-w-0 flex-1">
                                        <p className="font-medium">{s.label}</p>
                                        {s.title && (
                                            <p
                                                className="truncate text-sm text-muted-foreground"
                                                translate="no"
                                            >
                                                {s.title}
                                            </p>
                                        )}
                                    </div>
                                    <span
                                        className={`text-sm ${s.is_visible ? 'text-green-700' : 'text-muted-foreground'}`}
                                    >
                                        {s.is_visible
                                            ? t('content.visible')
                                            : t('content.hidden')}
                                    </span>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        aria-label={
                                            s.is_visible
                                                ? t('content.hide')
                                                : t('content.show')
                                        }
                                        onClick={() =>
                                            router.visit(
                                                contentRoutes.visibility(s.id),
                                                { preserveScroll: true },
                                            )
                                        }
                                    >
                                        {s.is_visible ? (
                                            <EyeOff className="size-4" />
                                        ) : (
                                            <Eye className="size-4" />
                                        )}
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        asChild
                                        aria-label={t('content.edit_section')}
                                    >
                                        <Link href={contentRoutes.edit(s.id)}>
                                            <Pencil className="size-4" />
                                        </Link>
                                    </Button>
                                </li>
                            ))}
                        </ul>
                    </section>
                ))}
            </div>
        </>
    );
}
