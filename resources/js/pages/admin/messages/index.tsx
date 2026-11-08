import { Head, Link, router } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import { useState } from 'react';
import { ConfirmDialog } from '@/components/admin/confirm-dialog';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/lib/i18n';
import messageRoutes from '@/routes/admin/messages';

type Row = {
    id: number;
    name: string;
    email: string;
    subject: string;
    excerpt: string;
    status: string;
    status_label: string;
    received: string;
};
type Option = { value: string; label: string };
type Filters = { q?: string; status?: string; subject?: string };
type Props = {
    messages: {
        data: Row[];
        prev_url: string | null;
        next_url: string | null;
        current_page: number;
        last_page: number;
    };
    filters: Filters;
    statuses: Option[];
    subjects: Option[];
    retentionMonths: number;
};

// F-AMSG-01/03 (sprint 9) : liste, recherche, filtres, suppression multiple
export default function MessagesIndex({
    messages,
    filters,
    statuses,
    subjects,
    retentionMonths,
}: Props) {
    const t = useTranslations();
    const [q, setQ] = useState(filters.q ?? '');
    const [selected, setSelected] = useState<number[]>([]);

    const apply = (next: Partial<Filters>) =>
        router.get(
            messageRoutes.index().url,
            { ...filters, ...next },
            { preserveState: true, replace: true },
        );
    const toggle = (id: number) =>
        setSelected((s) =>
            s.includes(id) ? s.filter((x) => x !== id) : [...s, id],
        );

    return (
        <>
            <Head title={t('messages_admin.title')} />
            <div className="flex flex-col gap-6 p-4 md:p-8">
                <div>
                    <h1 className="text-2xl font-semibold">
                        {t('messages_admin.title')}
                    </h1>
                    <p className="mt-1 text-muted-foreground">
                        {t('messages_admin.intro')}{' '}
                        {t('messages_admin.retention', {
                            months: retentionMonths,
                        })}
                    </p>
                </div>

                <div className="flex flex-wrap gap-3">
                    <input
                        value={q}
                        translate="no"
                        placeholder={t('messages_admin.search')}
                        aria-label={t('messages_admin.search')}
                        onChange={(e) => setQ(e.target.value)}
                        onKeyDown={(e) => {
                            if (e.key === 'Enter') {
                                apply({ q: q || undefined });
                            }
                        }}
                        className="h-9 w-64 max-w-full rounded-md border border-input bg-transparent px-3 text-sm"
                    />
                    <select
                        value={filters.status ?? ''}
                        aria-label={t('messages_admin.status')}
                        onChange={(e) =>
                            apply({ status: e.target.value || undefined })
                        }
                        className="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                    >
                        <option value="">
                            {t('messages_admin.status')}:{' '}
                            {t('messages_admin.all')}
                        </option>
                        {statuses.map((s) => (
                            <option key={s.value} value={s.value}>
                                {s.label}
                            </option>
                        ))}
                    </select>
                    <select
                        value={filters.subject ?? ''}
                        aria-label={t('messages_admin.subject')}
                        onChange={(e) =>
                            apply({ subject: e.target.value || undefined })
                        }
                        className="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                    >
                        <option value="">
                            {t('messages_admin.subject')}:{' '}
                            {t('messages_admin.all')}
                        </option>
                        {subjects.map((s) => (
                            <option key={s.value} value={s.value}>
                                {s.label}
                            </option>
                        ))}
                    </select>
                    {selected.length > 0 && (
                        <ConfirmDialog
                            title={t('messages_admin.delete_selected')}
                            trigger={
                                <Button variant="destructive" size="sm">
                                    <Trash2 className="size-4" />{' '}
                                    {t('messages_admin.delete_selected')} (
                                    {selected.length})
                                </Button>
                            }
                            onConfirm={() =>
                                router.post(
                                    messageRoutes.destroyMany().url,
                                    { ids: selected },
                                    { onSuccess: () => setSelected([]) },
                                )
                            }
                        />
                    )}
                </div>

                {messages.data.length === 0 ? (
                    <p className="text-muted-foreground">
                        {t('messages_admin.empty')}
                    </p>
                ) : (
                    <ul className="divide-y rounded-lg border">
                        {messages.data.map((m) => (
                            <li
                                key={m.id}
                                className="flex items-start gap-3 p-3"
                            >
                                <input
                                    type="checkbox"
                                    className="mt-1.5 size-4"
                                    checked={selected.includes(m.id)}
                                    onChange={() => toggle(m.id)}
                                    aria-label={t('messages_admin.select', {
                                        name: m.name,
                                    })}
                                />
                                <Link
                                    href={messageRoutes.show(m.id)}
                                    className="min-w-0 flex-1"
                                >
                                    <p
                                        className={`truncate ${m.status === 'new' ? 'font-semibold' : ''}`}
                                        translate="no"
                                    >
                                        {m.name} — {m.email}
                                    </p>
                                    <p
                                        className="truncate text-sm text-muted-foreground"
                                        translate="no"
                                    >
                                        {m.excerpt}
                                    </p>
                                </Link>
                                <div className="shrink-0 text-right text-sm">
                                    <p>{m.status_label}</p>
                                    <p className="text-muted-foreground">
                                        {m.subject} · {m.received}
                                    </p>
                                </div>
                            </li>
                        ))}
                    </ul>
                )}

                {/* Pagination simple : sans HTML venu du serveur (pas de dangerouslySetInnerHTML) */}
                {messages.last_page > 1 && (
                    <nav
                        aria-label={t('messages_admin.pagination')}
                        className="flex items-center gap-3 text-sm"
                    >
                        {messages.prev_url && (
                            <Link href={messages.prev_url} preserveScroll>
                                ‹ {t('actions.back')}
                            </Link>
                        )}
                        <span className="text-muted-foreground">
                            {messages.current_page} / {messages.last_page}
                        </span>
                        {messages.next_url && (
                            <Link href={messages.next_url} preserveScroll>
                                ›
                            </Link>
                        )}
                    </nav>
                )}
            </div>
        </>
    );
}
