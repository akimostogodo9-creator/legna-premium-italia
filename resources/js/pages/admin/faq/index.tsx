import { Head, Link, router } from '@inertiajs/react';
import { AlertTriangle, Home, Pencil, Plus, Trash2 } from 'lucide-react';
import { ConfirmDialog } from '@/components/admin/confirm-dialog';
import { SortableList } from '@/components/admin/sortable-list';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/lib/i18n';
import faqRoutes from '@/routes/admin/faq';

type Row = {
    id: number;
    question: string;
    topic: string | null;
    is_active: boolean;
    show_on_home: boolean;
    is_placeholder: boolean;
};

// F-AFAQ-01 (sprint 8) : liste, ordre par glisser-déposer, réponses provisoires signalées
export default function FaqIndex({ faqs }: { faqs: Row[] }) {
    const t = useTranslations();

    return (
        <>
            <Head title={t('faq.title')} />
            <div className="flex max-w-4xl flex-col gap-6 p-4 md:p-8">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <h1 className="text-2xl font-semibold">{t('faq.title')}</h1>
                    <Button asChild>
                        <Link href={faqRoutes.create()}>
                            <Plus className="size-4" /> {t('faq.new')}
                        </Link>
                    </Button>
                </div>
                <p className="text-sm text-muted-foreground">
                    {t('catalog.order_hint')}
                </p>

                {faqs.length === 0 ? (
                    <p className="text-muted-foreground">
                        {t('catalog.empty')}
                    </p>
                ) : (
                    <SortableList
                        items={faqs}
                        onReorder={(ids) =>
                            router.post(
                                faqRoutes.reorder().url,
                                { ids },
                                { preserveScroll: true },
                            )
                        }
                        renderItem={(faq, handle) => (
                            <div className="mb-2 flex items-center gap-3 rounded-lg border bg-background p-3">
                                {handle}
                                <div className="min-w-0 flex-1">
                                    <p
                                        className="truncate font-medium"
                                        translate="no"
                                    >
                                        {faq.question}
                                    </p>
                                    <p className="flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
                                        <span>
                                            {faq.is_active
                                                ? t('faq.active')
                                                : t('faq.inactive')}
                                        </span>
                                        {faq.topic && (
                                            <span translate="no">
                                                — {faq.topic}
                                            </span>
                                        )}
                                        {faq.show_on_home && (
                                            <Home
                                                className="size-4"
                                                aria-label={t(
                                                    'faq.show_on_home',
                                                )}
                                            />
                                        )}
                                        {faq.is_placeholder && (
                                            <span className="flex items-center gap-1 text-amber-700">
                                                <AlertTriangle className="size-4" />{' '}
                                                {t('faq.placeholder_warning')}
                                            </span>
                                        )}
                                    </p>
                                </div>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    asChild
                                    aria-label={t('actions.edit')}
                                >
                                    <Link href={faqRoutes.edit(faq.id)}>
                                        <Pencil className="size-4" />
                                    </Link>
                                </Button>
                                <ConfirmDialog
                                    title={t('actions.delete')}
                                    trigger={
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            aria-label={t('actions.delete')}
                                        >
                                            <Trash2 className="size-4" />
                                        </Button>
                                    }
                                    onConfirm={() =>
                                        router.visit(
                                            faqRoutes.destroy(faq.id),
                                            {
                                                preserveScroll: true,
                                            },
                                        )
                                    }
                                />
                            </div>
                        )}
                    />
                )}
            </div>
        </>
    );
}
