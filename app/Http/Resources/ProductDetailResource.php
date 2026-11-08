<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\Ordering\OrderLinkBuilder;
use App\Services\Ordering\WhatsAppLinkBuilder;
use App\Support\ImagePresenter;
use Illuminate\Http\Request;

/**
 * Fiche produit complète (F-PROD).
 *
 * @mixin Product
 */
class ProductDetailResource extends ProductCardResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'description' => $this->description, // HTML déjà nettoyé à l'enregistrement (sprint 4)
            'images' => $this->images
                ->map(fn (ProductImage $image) => ImagePresenter::present($image, $this->name))
                ->values()
                ->all(),
            'specs' => $this->specs(),
            'is_active' => $this->is_active,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description ?? $this->short_description,
            // R-WA-03 (sprint 6) : WhatsApp avec le nom du produit ; null sans numéro
            'whatsapp_url' => app(WhatsAppLinkBuilder::class)->forProduct($this->resource, app(OrderLinkBuilder::class)),
        ];
    }

    /**
     * R-CAR-02 : uniquement les caractéristiques renseignées, dans l'ordre du CDC §11.
     *
     * @return list<array{key: string, value: string}>
     */
    private function specs(): array
    {
        $specs = [
            ['key' => 'wood_type', 'value' => $this->wood_type],
            ['key' => 'dimensions', 'value' => $this->dimensions],
            ['key' => 'packaging', 'value' => $this->packaging],
            ['key' => 'availability', 'value' => $this->availability->label()],
            ['key' => 'extra_info', 'value' => $this->extra_info],
        ];

        return array_values(array_filter($specs, fn (array $spec) => filled($spec['value'])));
    }
}
