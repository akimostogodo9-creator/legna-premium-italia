<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Services\Ordering\OrderLinkBuilder;
use App\Support\ImagePresenter;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Carte produit (F-CAT-05) — contrat de l'étape 6 (08-09 §2).
 * Sprint 6 : clé « order » (bouton Ordina) ajoutée.
 *
 * @mixin Product
 */
class ProductCardResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'url' => route('products.show', $this->slug, false),
            'short_description' => $this->short_description,
            'category' => $this->category ? ['name' => $this->category->name, 'slug' => $this->category->slug] : null,
            // R-PRIX-02/03 : prix formaté à l'italienne, ou « su richiesta »
            'price_label' => Money::format($this->price),
            'unit' => $this->price ? $this->saleUnit?->name : null,
            'price_on_request' => $this->isPriceOnRequest(),
            'availability' => [
                'value' => $this->availability->value,
                'label' => $this->availability->label(),
            ],
            'image' => ImagePresenter::present($this->mainImage(), $this->name),
            // ★ R-ORDINA : null si l'e-mail de commande n'est pas configuré
            'order' => app(OrderLinkBuilder::class)->forProduct($this->resource),
        ];
    }
}
