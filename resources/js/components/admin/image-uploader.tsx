import { router } from '@inertiajs/react';
import type { UrlMethodPair } from '@inertiajs/core';
import { ImagePlus } from 'lucide-react';
import { useRef, useState } from 'react';
import type { DragEvent } from 'react';
import InputError from '@/components/input-error';
import { useTranslations } from '@/lib/i18n';

type Props = { upload: UrlMethodPair; maxMb: number; error?: string };

// F-APROD-03 : envoi multiple par glisser-déposer ou clic
export function ImageUploader({ upload, maxMb, error }: Props) {
    const t = useTranslations();
    const input = useRef<HTMLInputElement>(null);
    const [dragging, setDragging] = useState(false);
    const [uploading, setUploading] = useState(false);

    const send = (files: FileList | null) => {
        if (!files || files.length === 0) {
            return;
        }

        router.post(
            upload.url,
            { photos: Array.from(files) },
            {
                forceFormData: true,
                preserveScroll: true,
                onStart: () => setUploading(true),
                onFinish: () => {
                    setUploading(false);

                    if (input.current) {
                        input.current.value = '';
                    }
                },
            },
        );
    };

    const onDrop = (event: DragEvent) => {
        event.preventDefault();
        setDragging(false);
        send(event.dataTransfer.files);
    };

    return (
        <div className="grid gap-2">
            <button
                type="button"
                onClick={() => input.current?.click()}
                onDragOver={(e) => {
                    e.preventDefault();
                    setDragging(true);
                }}
                onDragLeave={() => setDragging(false)}
                onDrop={onDrop}
                disabled={uploading}
                className={`flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed px-6 py-10 text-center transition-colors ${
                    dragging
                        ? 'border-primary bg-muted'
                        : 'border-input hover:bg-muted/50'
                }`}
            >
                <ImagePlus className="size-8 text-muted-foreground" />
                <span className="font-medium">
                    {uploading ? t('photos.uploading') : t('photos.drop')}
                </span>
                <span className="text-sm text-muted-foreground">
                    {t('photos.formats', { mb: maxMb })}
                </span>
            </button>
            <input
                ref={input}
                type="file"
                accept="image/jpeg,image/png,image/webp"
                multiple
                hidden
                onChange={(e) => send(e.target.files)}
            />
            <InputError message={error} />
        </div>
    );
}
