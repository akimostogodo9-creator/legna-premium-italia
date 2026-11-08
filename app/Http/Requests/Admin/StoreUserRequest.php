<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

// F-AUSR-02 : création d'un administrateur
class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null; // la route est déjà réservée aux admins actifs
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:191', 'unique:users,email'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('admin.users.name'),
            'email' => __('admin.users.email'),
        ];
    }
}
