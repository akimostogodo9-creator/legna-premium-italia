<?php

namespace App\Models;

use App\Enums\Availability;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

// R-PROD, R-PRIX, R-CAR, R-ORD
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'slug', 'short_description', 'description',
        'price', 'sale_unit_id', 'wood_type', 'dimensions', 'packaging',
        'availability', 'extra_info', 'is_featured', 'is_active', 'sort_order',
        'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            // 'decimal:2' renvoie une chaîne ("129.00") : jamais de float pour un prix
            'price' => 'decimal:2',
            'availability' => Availability::class,
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function saleUnit(): BelongsTo
    {
        return $this->belongsTo(SaleUnit::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /** R-IMG-01 : photo principale (si aucune n'est marquée, voir mainImage()). */
    public function mainImageRelation(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_main', true);
    }

    public function dailyStats(): HasMany
    {
        return $this->hasMany(ProductDailyStat::class);
    }

    /** Photo principale, ou la première par ordre si aucune n'est marquée (étape 4 §4.4). */
    public function mainImage(): ?ProductImage
    {
        return $this->mainImageRelation ?? $this->images->first();
    }

    /** R-PRIX-03 : vrai si le site doit afficher « Prezzo su richiesta ». */
    public function isPriceOnRequest(): bool
    {
        return $this->price === null;
    }

    /** R-CAR-04 : un produit épuisé reste visible avec « Chiedi disponibilità ». */
    public function isOutOfStock(): bool
    {
        return $this->availability === Availability::OutOfStock;
    }

    /** R-PROD-03 + R-CAT-03 : produit actif, sans catégorie ou dans une catégorie active. */
    public function scopeVisible(Builder $query): void
    {
        $query->where('is_active', true)
            ->where(function (Builder $q) {
                $q->whereNull('category_id')
                    ->orWhereHas('category', fn (Builder $c) => $c->where('is_active', true));
            });
    }

    /** R-ORD-02 : produits mis en avant sur l'accueil. */
    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /** R-ORD-01 : ordre défini par l'admin. */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
