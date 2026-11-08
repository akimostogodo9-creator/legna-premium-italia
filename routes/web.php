<?php

use App\Http\Controllers\Public\HomeController;
use Illuminate\Support\Facades\Route;

// Site public (F-HOME) — page provisoire du sprint 0, contenu réel aux sprints 3 et 7
Route::get('/', HomeController::class)->name('home');

// L'administration est dans routes/admin.php (/admin).
// Sprint 2 : les pages de paramètres du kit (routes/settings.php) sont remplacées par /admin/profilo.
