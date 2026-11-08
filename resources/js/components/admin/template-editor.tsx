import { useRef } from 'react';
import InputError from '@/components/input-error';
import { Label } from '@/components/ui/label';
import { useTranslations } from '@/lib/i18n';

type Props = {
    id: string;
    label: string;
    value: string;
    onChange: (value: string) => void;
    variables: readonly string[];
    rows?: number;
    error?: string;
};

// F-ASET-04 : modèle de texte + variables cliquables insérées à l'emplacement du curseur
export function TemplateEditor({
    id,
    label,
    value,
    onChange,
    variables,
    rows = 10,
    error,
}: Props) {
    const t = useTranslations();
    const area = useRef<HTMLTextAreaElement>(null);

    const insert = (variable: string) => {
        const el = area.current;
        const token = `{${variable}}`;

        if (!el) {
            onChange(value + token);

            return;
        }

        const start = el.selectionStart ?? value.length;
        const end = el.selectionEnd ?? value.length;
        onChange(value.slice(0, start) + token + value.slice(end));

        requestAnimationFrame(() => {
            el.focus();
            el.setSelectionRange(start + token.length, start + token.length);
        });
    };

    return (
        <div className="grid gap-2">
            <Label htmlFor={id}>{label}</Label>
            <div className="flex flex-wrap items-center gap-1.5">
                <span className="text-xs text-muted-foreground">
                    {t('settings.variables')}
                </span>
                {variables.map((v) => (
                    <button
                        key={v}
                        type="button"
                        onClick={() => insert(v)}
                        translate="no"
                        className="rounded-md border px-2 py-0.5 font-mono text-xs hover:bg-muted"
                    >{`{${v}}`}</button>
                ))}
            </div>
            <textarea
                ref={area}
                id={id}
                name={id}
                rows={rows}
                value={value}
                translate="no"
                aria-invalid={!!error}
                onChange={(e) => onChange(e.target.value)}
                className="w-full rounded-md border border-input bg-transparent px-3 py-2 font-mono text-sm shadow-xs focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none aria-invalid:border-red-500"
            />
            <InputError message={error} />
        </div>
    );
}
