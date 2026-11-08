<?php

namespace App\Http\Requests\Public;

use App\Enums\ContactSubject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * R-CONT-01/02 (sprint 9) : renseignements uniquement — aucun champ de commande.
 * « website » est un champ PIÈGE invisible : un humain le laisse vide, un robot le remplit (R-CONT-05).
 */
class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'phone' => $this->filled('phone') ? trim((string) $this->input('phone')) : null,
            'message' => trim((string) $this->input('message')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'string', 'email:rfc', 'max:191'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[\d\s+().\/-]+$/'],
            'subject' => ['required', Rule::enum(ContactSubject::class)],
            'message' => ['required', 'string', 'min:20', 'max:2000'],
            'privacy' => ['accepted'],
            'website' => ['nullable', 'string', 'max:255'], // piège
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'privacy.accepted' => __('public.contact.privacy_required'),
            'phone.regex' => __('public.contact.invalid_phone'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('public.contact.name'),
            'email' => __('public.contact.email_field'),
            'phone' => __('public.contact.phone_field'),
            'subject' => __('public.contact.subject'),
            'message' => __('public.contact.message'),
        ];
    }

    public function isSpamTrap(): bool
    {
        return $this->filled('website');
    }
}
