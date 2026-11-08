<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PlaceholderController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
| Administration — préfixe /admin, noms « admin.* » (étape 3 §2.2)
| Enregistré dans bootstrap/app.php (middleware « web »).
| Les pages de connexion (/admin/accesso…) sont créées par Fortify (config/fortify.php).
| Tout le reste est réservé aux administrateurs connectés ET actifs (sprint 2).
*/
Route::middleware(['auth', 'admin.active'])->group(function () {

    // F-DASH : provisoire, complété au sprint 10
    Route::get('/', DashboardController::class)->name('dashboard');

    // Comptes administrateurs (F-AUSR-01/02)
    Route::get('/utenti', [UserController::class, 'index'])->name('users.index');
    Route::get('/utenti/nuovo', [UserController::class, 'create'])->name('users.create');
    Route::post('/utenti', [UserController::class, 'store'])->name('users.store');
    Route::patch('/utenti/{user}/stato', [UserController::class, 'toggleActive'])->name('users.toggle');
    Route::delete('/utenti/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Mon profil (F-AUTH-04)
    Route::get('/profilo', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profilo', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profilo/password', [ProfileController::class, 'updatePassword'])
        ->middleware('throttle:6,1')
        ->name('profile.password');

    // Sections des sprints suivants : page provisoire (remplacée au fur et à mesure)
    foreach ([
        'prodotti' => 'products', 'categorie' => 'categories', 'contenuti' => 'content',
        'faq' => 'faq', 'pagine-legali' => 'legal', 'impostazioni' => 'settings', 'messaggi' => 'messages',
    ] as $uri => $name) {
        Route::get("/{$uri}", PlaceholderController::class)->name("{$name}.index")->defaults('section', $name);
    }
});
