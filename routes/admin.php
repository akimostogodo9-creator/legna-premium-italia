<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\ContentSectionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\IdentityController;
use App\Http\Controllers\Admin\LegalPageController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SaleUnitController;
use App\Http\Controllers\Admin\SettingController;
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

    // Paramètres (F-ASET) — sprint 6 : contacts et modèles du message « Ordina »
    Route::get('/impostazioni', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/impostazioni/contatti-e-ordini', [SettingController::class, 'updateOrder'])->name('settings.order');
    Route::put('/impostazioni/azienda', [SettingController::class, 'updateCompany'])->name('settings.company'); // sprint 8
    Route::put('/impostazioni/modulo', [SettingController::class, 'updateForm'])->name('settings.form'); // sprint 9

    // Contenus (F-ACONT) — sprint 7. « identita » déclaré AVANT « {section} »
    Route::get('/contenuti', [ContentSectionController::class, 'index'])->name('content.index');
    Route::get('/contenuti/identita', [IdentityController::class, 'edit'])->name('identity.edit');
    Route::post('/contenuti/identita', [IdentityController::class, 'update'])->name('identity.update');
    Route::get('/contenuti/{section}', [ContentSectionController::class, 'edit'])->name('content.edit')->whereNumber('section');
    Route::post('/contenuti/{section}', [ContentSectionController::class, 'update'])->name('content.update')->whereNumber('section'); // POST : fichiers
    Route::patch('/contenuti/{section}/visibilita', [ContentSectionController::class, 'toggleVisibility'])->name('content.visibility')->whereNumber('section');

    // FAQ (F-AFAQ-01) — sprint 8
    Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');
    Route::get('/faq/nuova', [FaqController::class, 'create'])->name('faq.create');
    Route::post('/faq', [FaqController::class, 'store'])->name('faq.store');
    Route::post('/faq/ordine', [FaqController::class, 'reorder'])->name('faq.reorder');
    Route::get('/faq/{faq}/modifica', [FaqController::class, 'edit'])->name('faq.edit');
    Route::put('/faq/{faq}', [FaqController::class, 'update'])->name('faq.update');
    Route::delete('/faq/{faq}', [FaqController::class, 'destroy'])->name('faq.destroy');

    // Pages légales (F-ALEG-01) — sprint 8. {page:id} car le modèle utilise le slug pour le site public
    Route::get('/pagine-legali', [LegalPageController::class, 'index'])->name('legal.index');
    Route::get('/pagine-legali/{page:id}/modifica', [LegalPageController::class, 'edit'])->name('legal.edit');
    Route::put('/pagine-legali/{page:id}', [LegalPageController::class, 'update'])->name('legal.update');

    // Messages de contact (F-AMSG) — sprint 9
    Route::get('/messaggi', [ContactMessageController::class, 'index'])->name('messages.index');
    Route::post('/messaggi/elimina', [ContactMessageController::class, 'destroyMany'])->name('messages.destroy-many');
    Route::get('/messaggi/{message}', [ContactMessageController::class, 'show'])->name('messages.show')->whereNumber('message');
    Route::patch('/messaggi/{message}/gestito', [ContactMessageController::class, 'toggleHandled'])->name('messages.handled')->whereNumber('message');
    Route::delete('/messaggi/{message}', [ContactMessageController::class, 'destroy'])->name('messages.destroy')->whereNumber('message');
});
