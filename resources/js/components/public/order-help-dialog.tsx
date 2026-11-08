import { Check, Copy, X } from 'lucide-react';
import { useEffect, useEffectEvent, useRef, useState } from 'react';
import { copyText } from '@/lib/clipboard';
import { useTranslations } from '@/lib/i18n';
import { plainMessage } from '@/lib/mailto';
import type { OrderLink } from '@/types/catalog';
import { ButtonLink } from './button-link';
import { WhatsAppIcon } from './whatsapp-icon';

type Props = {
    order: OrderLink;
    whatsappUrl: string | null;
    onClose: () => void;
};

/**
 * ★ F-ORD-03 / R-REPLI-01→03 : s'affiche EN PLUS de l'ouverture de l'e-mail.
 * Rappelle que rien n'est envoyé sans « Invia », et propose copie / WhatsApp si rien ne s'ouvre.
 * Accessible : focus piégé dans la fenêtre, Échap ou clic extérieur pour fermer.
 */
export function OrderHelpDialog({ order, whatsappUrl, onClose }: Props) {
    const t = useTranslations();
    const panel = useRef<HTMLDivElement>(null);
    const [copied, setCopied] = useState<'email' | 'message' | null>(null);
    const [manual, setManual] = useState(false);
    const message = plainMessage(order.subject, order.body);

    // Sprint 6 : useEffectEvent (React 19.2+) → l'effet ne se relance pas si onClose change,
    // donc le focus n'est pas déplacé à chaque nouveau rendu.
    const close = useEffectEvent(() => onClose());

    useEffect(() => {
        const previous = document.activeElement as HTMLElement | null;
        panel.current?.querySelector<HTMLElement>('button')?.focus();

        const onKey = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                close();
            }

            if (event.key !== 'Tab' || !panel.current) {
                return;
            }

            const focusable = panel.current.querySelectorAll<HTMLElement>(
                'button, a[href], input, textarea',
            );
            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            }

            if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        };

        document.addEventListener('keydown', onKey);

        return () => {
            document.removeEventListener('keydown', onKey);
            previous?.focus();
        };
    }, []);

    const copy = async (what: 'email' | 'message') => {
        const ok = await copyText(what === 'email' ? order.email : message);

        if (ok) {
            setCopied(what);
            setTimeout(() => setCopied(null), 2500);
        } else {
            setManual(true);
        }
    };

    const copyButton = (what: 'email' | 'message', label: string) => (
        <button
            type="button"
            onClick={() => void copy(what)}
            className="inline-flex min-h-11 flex-1 items-center justify-center gap-2 rounded-full border border-legno-900/25 px-5 font-display font-semibold text-legno-900 hover:border-legno-900 hover:bg-crema-100"
        >
            {copied === what ? (
                <Check className="size-5 text-bosco-700" />
            ) : (
                <Copy className="size-5" />
            )}
            {copied === what ? t('order.copied') : label}
        </button>
    );

    return (
        <div
            className="fixed inset-0 z-50 flex items-end justify-center bg-legno-900/40 p-3 sm:items-center"
            onClick={onClose}
        >
            <div
                ref={panel}
                role="dialog"
                aria-modal="true"
                aria-labelledby="order-help-title"
                onClick={(e) => e.stopPropagation()}
                className="site relative max-h-[calc(100svh-1.5rem)] w-full max-w-lg overflow-y-auto rounded-[var(--radius-card)] bg-crema-50 p-6 shadow-[var(--shadow-float)] sm:p-8"
            >
                <button
                    type="button"
                    onClick={onClose}
                    aria-label={t('order.close')}
                    className="absolute top-3 right-3 inline-flex size-11 items-center justify-center rounded-full text-legno-900 hover:bg-crema-100"
                >
                    <X className="size-5" />
                </button>

                <h2 id="order-help-title" className="pr-10 text-2xl">
                    {t('order.help_title')}
                </h2>
                <p className="mt-3 text-legno-900/85">{t('order.help_text')}</p>
                <p className="mt-5 border-t border-crema-200 pt-5 text-[0.95rem] text-legno-700">
                    {t('order.help_fallback')}
                </p>

                <div className="mt-5 flex flex-col gap-2 sm:flex-row">
                    {copyButton('email', t('order.copy_address'))}
                    {copyButton('message', t('order.copy_message'))}
                </div>

                {manual && (
                    <div className="mt-4 grid gap-2">
                        <p className="text-sm text-legno-700">
                            {t('order.copy_manual')}
                        </p>
                        <label
                            htmlFor="order-help-email"
                            className="font-display text-sm font-medium"
                        >
                            {t('order.email_label')}
                        </label>
                        <input
                            id="order-help-email"
                            readOnly
                            value={order.email}
                            onFocus={(e) => e.target.select()}
                            className="h-11 rounded-[var(--radius-field)] border border-crema-200 bg-crema-100 px-3"
                        />
                        <label
                            htmlFor="order-help-message"
                            className="font-display text-sm font-medium"
                        >
                            {t('order.message_label')}
                        </label>
                        <textarea
                            id="order-help-message"
                            readOnly
                            value={message}
                            rows={6}
                            onFocus={(e) => e.target.select()}
                            className="rounded-[var(--radius-field)] border border-crema-200 bg-crema-100 p-3 text-sm"
                        />
                    </div>
                )}

                {whatsappUrl && (
                    <ButtonLink
                        href={whatsappUrl}
                        external
                        variant="whatsapp"
                        className="mt-3 w-full"
                    >
                        <WhatsAppIcon /> {t('order.whatsapp')}
                    </ButtonLink>
                )}
            </div>
        </div>
    );
}
