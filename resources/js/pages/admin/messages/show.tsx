import { Head, Link, router } from '@inertiajs/react';
import { ConfirmDialog } from '@/components/admin/confirm-dialog';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/lib/i18n';
import messageRoutes from '@/routes/admin/messages';

type Message = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    subject: string;
    message: string;
    status: string;
    status_label: string;
    received: string;
    handled_by: string | null;
    handled_at: string | null;
    reply_subject: string;
};

// F-AMSG-02 (sprint 9) : lecture, réponse par e-mail, traité / non traité, suppression
export default function MessageShow({ message }: { message: Message }) {
    const t = useTranslations();
    const reply = `mailto:${message.email}?subject=${encodeURIComponent(message.reply_subject)}`;

    return (
        <>
            <Head title={message.name} />
            <div className="flex max-w-3xl flex-col gap-6 p-4 md:p-8">
                <Link
                    href={messageRoutes.index()}
                    className="text-sm text-muted-foreground hover:underline"
                >
                    ← {t('messages_admin.title')}
                </Link>

                <div className="rounded-lg border p-5" translate="no">
                    <dl className="grid gap-2 text-sm sm:grid-cols-[8rem_1fr]">
                        <dt className="text-muted-foreground">
                            {t('messages_admin.from')}
                        </dt>
                        <dd className="break-words">
                            {message.name} — {message.email}
                            {message.phone && ` — ${message.phone}`}
                        </dd>
                        <dt className="text-muted-foreground">
                            {t('messages_admin.subject')}
                        </dt>
                        <dd>{message.subject}</dd>
                        <dt className="text-muted-foreground">
                            {t('messages_admin.received')}
                        </dt>
                        <dd>{message.received}</dd>
                        <dt className="text-muted-foreground">
                            {t('messages_admin.status')}
                        </dt>
                        <dd>
                            {message.status_label}
                            {message.handled_by &&
                                ` — ${t('messages_admin.handled_by', {
                                    name: message.handled_by,
                                    date: message.handled_at ?? '',
                                })}`}
                        </dd>
                    </dl>
                    {/* Texte brut : affiché tel quel, jamais interprété comme HTML (étape 7, XSS) */}
                    <p className="mt-5 border-t pt-5 break-words whitespace-pre-line">
                        {message.message}
                    </p>
                </div>

                <div className="flex flex-wrap gap-3">
                    <Button asChild>
                        <a href={reply}>{t('messages_admin.reply')}</a>
                    </Button>
                    <Button
                        variant="secondary"
                        onClick={() =>
                            router.visit(messageRoutes.handled(message.id), {
                                preserveScroll: true,
                            })
                        }
                    >
                        {message.status === 'handled'
                            ? t('messages_admin.mark_read')
                            : t('messages_admin.mark_handled')}
                    </Button>
                    <ConfirmDialog
                        title={t('actions.delete')}
                        trigger={
                            <Button variant="destructive">
                                {t('actions.delete')}
                            </Button>
                        }
                        onConfirm={() =>
                            router.visit(messageRoutes.destroy(message.id))
                        }
                    />
                </div>
            </div>
        </>
    );
}
