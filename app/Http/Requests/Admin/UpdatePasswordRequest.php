<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

// F-AUTH-04 : changement du mot de passe de l'admin connecté
class UpdatePasswordRequest extends FormRequest
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
        return [
            // F-AUTH-04 : l'ancien mot de passe est exigé
            'current_password' => ['required', 'current_password'],
            // Étape 7 : 12 caractères minimum, lettres et chiffres (règle commune,
            // définie dans AppServiceProvider — la même que pour « password dimenticata »)
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'current_password' => __('admin.profile.current_password'),
            'password' => __('admin.profile.new_password'),
        ];
    }
}
