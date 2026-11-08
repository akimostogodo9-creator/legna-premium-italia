import { Mail } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/lib/i18n';
import { buildMailto, MAILTO_SAFE_LENGTH } from '@/lib/mailto';

type Props = {
    email: string;
    subject: string;
    body: string;
    sample: Record<string, string>;
};

/** Remplace les variables par les valeurs d'exemple (même règle que TemplateRenderer côté serveur). */
export function renderTemplate(
    template: string,
    values: Record<string, string>,
): string {
    return template.replace(/\{([a-zA-Z_]+)\}/g, (match, name: string) =>
        name in values ? values[name] : match,
    );
}

// F-ASET-04/07 : aperçu en direct, compteur de longueur (R-ORDINA-08), bouton de test
export function OrderTemplatePreview({ email, subject, body, sample }: Props) {
    const t = useTranslations();
    const renderedSubject = renderTemplate(subject, sample);
    const renderedBody = renderTemplate(body, sample);
    const href = email
        ? buildMailto(email, renderedSubject, renderedBody)
        : null;
    const length =
        href?.length ??
        buildMailto('x@x.it', renderedSubject, renderedBody).length;
    const tooLong = length > MAILTO_SAFE_LENGTH;

    return (
        <div className="grid content-start gap-3 rounded-lg border bg-muted/30 p-4">
            <p className="text-sm font-medium">{t('settings.preview')}</p>
            <div
                className="rounded-md bg-background p-3 text-sm"
                translate="no"
            >
                <p className="font-semibold">{renderedSubject}</p>
                <p className="mt-2 whitespace-pre-line">{renderedBody}</p>
            </div>
            <p
                className={`text-xs ${tooLong ? 'font-medium text-red-600' : 'text-muted-foreground'}`}
            >
                {t('settings.length', {
                    count: length,
                    max: MAILTO_SAFE_LENGTH,
                })}
                {tooLong && <> — {t('settings.too_long')}</>}
            </p>
            {href && (
                <div className="flex flex-wrap items-center gap-3">
                    <Button asChild variant="secondary" size="sm">
                        <a href={href}>
                            <Mail className="size-4" />{' '}
                            {t('settings.test_button')}
                        </a>
                    </Button>
                    <span className="text-xs text-muted-foreground">
                        {t('settings.test_hint')}
                    </span>
                </div>
            )}
        </div>
    );
}
