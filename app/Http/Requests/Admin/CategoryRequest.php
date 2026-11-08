<?php

namespace App\Http\Requests\Admin;

use App\Services\Settings\SettingsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

// F-ACAT-02 : création / modification d'une catégorie
class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->filled('slug') ? Str::slug($this->input('slug')) : null,
            'is_active' => $this->boolean('is_active'),
            'remove_image' => $this->boolean('remove_image'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxKb = app(SettingsService::class)->get('image_max_mb', 8) * 1024;
        $category = $this->route('category');

        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:140', Rule::unique('categories', 'slug')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'meta_title' => ['nullable', 'string', 'max:191'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            // R-IMG-02 + étape 7 : type vérifié sur le contenu, taille et dimensions limitées
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', "max:{$maxKb}", 'dimensions:max_width=8000,max_height=8000'],
            'remove_image' => ['boolean'],
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
            'image' => __('admin.catalog.image'),
        ];
    }

    /**
     * Données à enregistrer (sans les champs techniques).
     *
     * @return array<string, mixed>
     */
    public function categoryData(): array
    {
        return $this->safe()->only(['name', 'slug', 'description', 'meta_title', 'meta_description', 'is_active']);
    }
}
