<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

// F-AFAQ-01 (sprint 8) : création / modification d'une question
class FaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'show_on_home' => $this->boolean('show_on_home'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:10000'],
            'topic' => ['nullable', 'string', 'max:80'],
            'show_on_home' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'question' => __('admin.faq.question'),
            'answer' => __('admin.faq.answer'),
            'topic' => __('admin.faq.topic'),
        ];
    }
}
