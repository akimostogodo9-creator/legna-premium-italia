<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

// R-ADM-01/02 : comptes administrateurs
class AdminUserService
{
    /**
     * Crée un administrateur avec un mot de passe aléatoire inconnu de tous,
     * puis lui envoie un lien pour choisir son propre mot de passe.
     */
    public function create(string $name, string $email): User
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(Str::random(64)),
            'is_active' => true,
        ]);

        // Créé par un admin : l'adresse est considérée comme vérifiée
        $user->forceFill(['email_verified_at' => now()])->save();

        Password::sendResetLink(['email' => $user->email]);

        return $user;
    }

    /** Active / désactive un compte (jamais soi-même, jamais le dernier actif). */
    public function toggleActive(User $target, User $actor): void
    {
        DB::transaction(function () use ($target, $actor) {
            if ($target->is_active) {
                $this->ensureCanRemove($target, $actor);
            }

            $target->update(['is_active' => ! $target->is_active]);
        });
    }

    public function delete(User $target, User $actor): void
    {
        DB::transaction(function () use ($target, $actor) {
            $this->ensureCanRemove($target, $actor);
            $target->delete();
        });
    }

    /** R-ADM-02 */
    private function ensureCanRemove(User $target, User $actor): void
    {
        if ($target->is($actor)) {
            throw ValidationException::withMessages(['user' => __('admin.users.cannot_change_self')]);
        }

        // Verrou : deux admins ne peuvent pas se désactiver mutuellement en même temps
        $activeCount = User::query()->where('is_active', true)->lockForUpdate()->count();

        if ($target->is_active && $activeCount <= 1) {
            throw ValidationException::withMessages(['user' => __('admin.users.cannot_remove_last')]);
        }
    }
}
