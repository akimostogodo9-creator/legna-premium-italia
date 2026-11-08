<?php

use App\Http\Controllers\Public\HomeController;
use Illuminate\Support\Facades\Route;

// Site public (F-HOME) — page provisoire du sprint 0, contenu réel aux sprints 3 et 7
Route::get('/', HomeController::class)->name('home');

// Le tableau de bord est dans routes/admin.php (/admin) — T6, sprint 0

// Paramètres du compte fournis par le kit (revus au sprint 2)
require __DIR__.'/settings.php';
