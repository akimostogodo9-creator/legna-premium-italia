<?php

namespace App\Http\Requests\Admin;

use App\Services\Settings\SettingsService;
use Illuminate\Foundation\Http\FormRequest;

// F-APROD-03 : envoi de photos
class ProductImageUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxKb = app(SettingsService::class)->get('image_max_mb', 8) * 1024;

        return [
            'photos' => ['required', 'array', 'min:1', 'max:20'],
            // Étape 7 : type vérifié sur le CONTENU du fichier (pas seulement l'extension)
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', "max:{$maxKb}", 'dimensions:max_width=8000,max_height=8000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['photos.*' => __('admin.products.photos')];
    }
}
