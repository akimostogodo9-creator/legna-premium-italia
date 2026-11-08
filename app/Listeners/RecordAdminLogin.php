<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

// F-AUSR-01 : date du dernier accès affichée dans la liste des administrateurs
class RecordAdminLogin
{
    public function handle(Login $event): void
    {
        $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
    }
}
