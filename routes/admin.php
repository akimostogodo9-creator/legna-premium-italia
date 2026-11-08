<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PlaceholderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SaleUnitController;
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

    // Catégories (F-ACAT) — {category:id} : dans l'admin on utilise l'identifiant, pas le slug
    Route::get('/categorie', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categorie/nuova', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categorie', [CategoryController::class, 'store'])->name('categories.store');
    Route::post('/categorie/ordine', [CategoryController::class, 'reorder'])->name('categories.reorder');
    Route::get('/categorie/{category:id}/modifica', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::post('/categorie/{category:id}', [CategoryController::class, 'update'])->name('categories.update'); // POST : envoi de fichier
    Route::delete('/categorie/{category:id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Unités de vente (déclarées AVANT /prodotti/{product:id})
    Route::get('/prodotti/unita', [SaleUnitController::class, 'index'])->name('units.index');
    Route::post('/prodotti/unita', [SaleUnitController::class, 'store'])->name('units.store');
    Route::patch('/prodotti/unita/{unit}', [SaleUnitController::class, 'update'])->name('units.update');
    Route::delete('/prodotti/unita/{unit}', [SaleUnitController::class, 'destroy'])->name('units.destroy');

    // Produits (F-APROD)
    Route::get('/prodotti', [ProductController::class, 'index'])->name('products.index');
    Route::get('/prodotti/nuovo', [ProductController::class, 'create'])->name('products.create');
    Route::post('/prodotti', [ProductController::class, 'store'])->name('products.store');
    Route::post('/prodotti/ordine', [ProductController::class, 'reorder'])->name('products.reorder');
    Route::get('/prodotti/{product:id}/modifica', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/prodotti/{product:id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/prodotti/{product:id}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::patch('/prodotti/{product:id}/pubblicazione', [ProductController::class, 'toggleActive'])->name('products.toggle');

    // Photos (F-APROD-03)
    Route::post('/prodotti/{product:id}/foto', [ProductImageController::class, 'store'])->name('products.images.store');
    Route::post('/prodotti/{product:id}/foto/ordine', [ProductImageController::class, 'reorder'])->name('products.images.reorder');
    Route::patch('/foto/{image}/principale', [ProductImageController::class, 'setMain'])->name('images.main');
    Route::patch('/foto/{image}', [ProductImageController::class, 'updateAlt'])->name('images.alt');
    Route::delete('/foto/{image}', [ProductImageController::class, 'destroy'])->name('images.destroy');

    // Sections des sprints suivants : page provisoire (remplacée au fur et à mesure)
    // Sprint 4 : « prodotti » et « categorie » ont leurs vraies pages (plus haut)
    foreach ([
        'contenuti' => 'content', 'faq' => 'faq', 'pagine-legali' => 'legal',
        'impostazioni' => 'settings', 'messaggi' => 'messages',
    ] as $uri => $name) {
        Route::get("/{$uri}", PlaceholderController::class)->name("{$name}.index")->defaults('section', $name);
    }
});
