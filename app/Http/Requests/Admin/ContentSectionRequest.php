<?php

namespace App\Http\Requests\Admin;

use App\Models\ContentSection;
use App\Services\Content\ContentSectionRegistry;
use App\Services\Content\ContentSectionService;
use App\Services\Settings\SettingsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

/** F-ACONT (sprint 7) : règles de validation GÉNÉRÉES à partir du format de la section (registre). */
class ContentSectionRequest extends FormRequest
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
        /** @var ContentSection $section */
        $section = $this->route('section');
        $definition = app(ContentSectionRegistry::class)->get($section->page, $section->key);
        $maxKb = app(SettingsService::class)->get('image_max_mb', 8) * 1024;
        $rules = ['remove_images' => ['array'], 'remove_images.*' => ['string']];

        foreach ($definition['fields'] as $field => $type) {
            $required = in_array($field, $definition['required'], true) ? 'required' : 'nullable';

            $rules += match ($type) {
                'text' => [$field => [$required, 'string', 'max:255']],
                'textarea' => [$field => [$required, 'string', 'max:2000']],
                'richtext' => [$field => [$required, 'string', 'max:20000']],
                'image' => [
                    $field => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', "max:{$maxKb}", 'dimensions:max_width=8000,max_height=8000'],
                    $field.'_provisional' => ['boolean'],
                ],
                'items' => [
                    $field => ['array', 'max:4'],
                    "{$field}.*.icon" => ['nullable', Rule::in(ContentSectionService::ITEM_ICONS)],
                    "{$field}.*.title" => ['nullable', 'string', 'max:80'],
                    "{$field}.*.text" => ['nullable', 'string', 'max:300'],
                    "{$field}.*.visible" => ['nullable'],
                ],
                default => [],
            };
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return collect(trans('admin.content.fields'))->all();
    }

    /**
     * Fichiers image envoyés, par champ.
     *
     * @return array<string, UploadedFile>
     */
    public function imageFiles(): array
    {
        return collect($this->allFiles())->filter(fn ($file) => $file instanceof UploadedFile)->all();
    }
}
