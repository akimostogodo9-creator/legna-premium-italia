<?php

namespace App\Http\Requests\Admin;

use App\Rules\ItalianVatNumber;
use Illuminate\Foundation\Http\FormRequest;

// F-ASET-01 (sprint 8) : données de l'entreprise — affichées seulement si saisies (R-LEG-03)
class CompanySettingsRequest extends FormRequest
{
    public const FIELDS = [
        'company_name', 'company_address', 'company_vat', 'company_tax_code',
        'company_rea', 'company_phone', 'company_hours', 'delivery_area',
    ];

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'company_vat' => $this->filled('company_vat') ? ItalianVatNumber::normalize($this->input('company_vat')) : null,
            'company_tax_code' => $this->filled('company_tax_code') ? strtoupper((string) preg_replace('/\s+/', '', $this->input('company_tax_code'))) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_name' => ['nullable', 'string', 'max:191'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'company_vat' => ['nullable', new ItalianVatNumber],
            // Codice fiscale : 11 chiffres (société) ou 16 caractères (personne physique)
            'company_tax_code' => ['nullable', 'regex:/^(\d{11}|[A-Z]{6}\d{2}[A-Z]\d{2}[A-Z]\d{3}[A-Z])$/'],
            'company_rea' => ['nullable', 'string', 'max:30'],
            'company_phone' => ['nullable', 'string', 'max:30', 'regex:/^[\d\s+().\/-]+$/'],
            'company_hours' => ['nullable', 'string', 'max:300'],
            'delivery_area' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'company_tax_code.regex' => __('admin.settings.invalid_tax_code'),
            'company_phone.regex' => __('admin.settings.invalid_phone'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return collect(self::FIELDS)
            ->mapWithKeys(fn (string $key) => [$key => __('admin.settings.'.$key)])
            ->all();
    }
}
