import { router } from '@inertiajs/react';
import { Star, Trash2 } from 'lucide-react';
import { ConfirmDialog } from '@/components/admin/confirm-dialog';
import { ImageUploader } from '@/components/admin/image-uploader';
import { SortableList } from '@/components/admin/sortable-list';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/lib/i18n';
import imageRoutes from '@/routes/admin/images';
import productImages from '@/routes/admin/products/images';

export type AdminImage = {
    id: number;
    url: string;
    alt_text: string | null;
    is_main: boolean;
};

type Props = {
    productId: number;
    images: AdminImage[];
    maxMb: number;
    errors: Record<string, string>;
};

// F-APROD-03 : ordre, photo principale unique, texte alternatif, suppression
export function ProductPhotos({ productId, images, maxMb, errors }: Props) {
    const t = useTranslations();
    const uploadError = Object.entries(errors).find(([key]) =>
        key.startsWith('photos'),
    )?.[1];

    return (
        <div className="grid gap-4">
            <ImageUploader
                upload={productImages.store(productId)}
                maxMb={maxMb}
                error={uploadError}
            />

            {images.length === 0 && (
                <p className="text-sm text-amber-700">{t('photos.none')}</p>
            )}

            <SortableList
                items={images}
                onReorder={(ids) =>
                    router.post(
                        productImages.reorder(productId).url,
                        { ids },
                        { preserveScroll: true },
                    )
                }
                renderItem={(image, handle) => (
                    <div className="mb-2 flex flex-wrap items-center gap-3 rounded-lg border bg-background p-2">
                        {handle}
                        <img
                            src={image.url}
                            alt=""
                            className="size-16 shrink-0 rounded object-cover"
                        />
                        <input
                            defaultValue={image.alt_text ?? ''}
                            translate="no"
                            placeholder={t('photos.alt')}
                            aria-label={t('photos.alt')}
                            onBlur={(e) => {
                                if (e.target.value !== (image.alt_text ?? '')) {
                                    router.visit(imageRoutes.alt(image.id), {
                                        data: { alt_text: e.target.value },
                                        preserveScroll: true,
                                    });
                                }
                            }}
                            className="h-9 min-w-0 flex-1 rounded-md border border-input bg-transparent px-3 text-sm"
                        />
                        {image.is_main ? (
                            <span className="flex shrink-0 items-center gap-1 text-sm font-medium text-amber-700">
                                <Star className="size-4 fill-current" />{' '}
                                {t('photos.main')}
                            </span>
                        ) : (
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                onClick={() =>
                                    router.visit(imageRoutes.main(image.id), {
                                        preserveScroll: true,
                                    })
                                }
                            >
                                {t('photos.set_main')}
                            </Button>
                        )}
                        <ConfirmDialog
                            title={t('actions.delete')}
                            trigger={
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    aria-label={t('actions.delete')}
                                >
                                    <Trash2 className="size-4" />
                                </Button>
                            }
                            onConfirm={() =>
                                router.visit(imageRoutes.destroy(image.id), {
                                    preserveScroll: true,
                                })
                            }
                        />
                    </div>
                )}
            />
        </div>
    );
}
