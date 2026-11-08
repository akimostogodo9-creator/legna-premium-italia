<?php

use Illuminate\Support\Facades\Route;

/*
| Administration — enregistrée dans bootstrap/app.php avec :
|   préfixe d'URL « admin », préfixe de nom « admin. », middleware « web ».
| Les pages de connexion (/admin/accesso…) sont créées par Fortify (config/fortify.php).
*/

Route::middleware(['auth', 'verified'])->group(function () {
    // Tableau de bord provisoire du kit (T6, sprint 0) — remplacé au sprint 10
    Route::inertia('/', 'dashboard')->name('dashboard');
});
