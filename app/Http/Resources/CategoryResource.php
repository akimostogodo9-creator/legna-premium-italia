<?php

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Catégorie publique (F-CAT-03) — seuls ces champs partent vers React.
 *
 * @mixin Category
 */
class CategoryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'url' => route('products.category', $this->slug, false),
            'description' => $this->description,
            'image' => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
        ];
    }
}
