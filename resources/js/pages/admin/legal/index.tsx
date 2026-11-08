import { Head, Link } from '@inertiajs/react';
import { ExternalLink, Pencil } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/lib/i18n';
import legalAdmin from '@/routes/admin/legal';
import legalPublic from '@/routes/legal';

type Row = {
    id: number;
    slug: string;
    title: string;
    is_published: boolean;
    is_empty: boolean;
    updated_at: string | null;
};

// F-ALEG-01 (sprint 8) : les 5 pages légales et leur état
export default function LegalIndex({ pages }: { pages: Row[] }) {
    const t = useTranslations();

    return (
        <>
            <Head title={t('legal.title')} />
            <div className="flex max-w-3xl flex-col gap-6 p-4 md:p-8">
                <h1 className="text-2xl font-semibold">{t('legal.title')}</h1>
                <p className="rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                    {t('legal.intro')}
                </p>
                <ul className="divide-y rounded-lg border">
                    {pages.map((page) => (
                        <li
                            key={page.id}
                            className="flex items-center gap-3 p-3"
                        >
                            <div className="min-w-0 flex-1">
                                <p className="font-medium" translate="no">
                                    {page.title}
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    {page.is_empty
                                        ? t('legal.empty')
                                        : page.is_published
                                          ? t('legal.published')
                                          : t('legal.unpublished')}
                                    {page.updated_at &&
                                        ` — ${t('legal.updated')}: ${page.updated_at}`}
                                </p>
                            </div>
                            {page.is_published && (
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    asChild
                                    aria-label={t('legal.view')}
                                >
                                    <a
                                        href={legalPublic.show(page.slug).url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <ExternalLink className="size-4" />
                                    </a>
                                </Button>
                            )}
                            <Button
                                variant="ghost"
                                size="icon"
                                asChild
                                aria-label={t('actions.edit')}
                            >
                                <Link href={legalAdmin.edit(page.id)}>
                                    <Pencil className="size-4" />
                                </Link>
                            </Button>
                        </li>
                    ))}
                </ul>
            </div>
        </>
    );
}
