<?php

namespace App\Http\Requests\Admin;

use App\Enums\Availability;
use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

// F-APROD-02 : création / modification d'un produit
class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** Le prix saisi à l'italienne (« 1.234,56 ») est converti avant validation (R-PRIX, jamais de float). */
    protected function prepareForValidation(): void
    {
        $price = $this->input('price');

        try {
            $price = Money::parse(is_string($price) ? $price : null);
        } catch (InvalidArgumentException) {
            // on garde la saisie brute : la règle « numeric » renverra une erreur claire
        }

        $this->merge([
            'price' => $price,
            'slug' => $this->filled('slug') ? Str::slug($this->input('slug')) : null,
            'is_featured' => $this->boolean('is_featured'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:170', Rule::unique('products', 'slug')->ignore($product?->id)],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'short_description' => ['nullable', 'string', 'max:300'],
            'description' => ['nullable', 'string', 'max:20000'],
            // R-PRIX-01/05 : facultatif, positif, 2 décimales max ; une unité est obligatoire avec un prix
            'price' => ['nullable', 'numeric', 'gt:0', 'max:99999999.99'],
            'sale_unit_id' => ['nullable', 'integer', 'exists:sale_units,id', 'required_with:price'],
            'wood_type' => ['nullable', 'string', 'max:100'],
            'dimensions' => ['nullable', 'string', 'max:150'],
            'packaging' => ['nullable', 'string', 'max:150'],
            'availability' => ['required', Rule::enum(Availability::class)],
            'extra_info' => ['nullable', 'string', 'max:2000'],
            'is_featured' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:191'],
            'meta_description' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sale_unit_id.required_with' => __('admin.products.unit_required'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('admin.catalog.name'),
            'slug' => __('admin.catalog.slug'),
            'short_description' => __('admin.products.short_description'),
            'price' => __('admin.products.price'),
            'sale_unit_id' => __('admin.products.unit'),
            'availability' => __('admin.products.availability'),
        ];
    }
}
