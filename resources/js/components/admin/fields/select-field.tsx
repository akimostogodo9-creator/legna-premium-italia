import type { SelectHTMLAttributes } from 'react';
import InputError from '@/components/input-error';
import { Label } from '@/components/ui/label';

type Option = { value: string | number; label: string };

type Props = SelectHTMLAttributes<HTMLSelectElement> & {
    id: string;
    label: string;
    options: Option[];
    placeholder?: string;
    error?: string;
};

// Liste déroulante de l'admin — translate="no" sur les valeurs (F-ADM-00b)
export function SelectField({
    id,
    label,
    options,
    placeholder,
    error,
    ...props
}: Props) {
    return (
        <div className="grid gap-2">
            <Label htmlFor={id}>{label}</Label>
            <select
                id={id}
                name={id}
                aria-invalid={!!error}
                className="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm shadow-xs focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                {...props}
                translate="no"
            >
                {placeholder !== undefined && (
                    <option value="">{placeholder}</option>
                )}
                {options.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>
            <InputError message={error} />
        </div>
    );
}
