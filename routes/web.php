<?php

use App\Http\Controllers\Public\ContentPageController;
use App\Http\Controllers\Public\FaqController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\LegalPageController;
use App\Http\Controllers\Public\PlaceholderController;
use App\Http\Controllers\Public\ProductController;
use App\Http\Controllers\Public\StatsController;
use Illuminate\Support\Facades\Route;

/*
| Site public — URLs en italien (étape 3 §2.1)
| L'administration est dans routes/admin.php (/admin).
*/
Route::get('/', HomeController::class)->name('home');

// Catalogue (F-CAT) et fiche produit (F-PROD) — sprint 5. L'ordre compte : « categoria » avant « {product} »
Route::get('/prodotti', [ProductController::class, 'index'])->name('products.index');
Route::get('/prodotti/categoria/{category:slug}', [ProductController::class, 'category'])->name('products.category');
Route::get('/prodotti/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// R-STAT-03 (sprint 6) : clics anonymes sur « Ordina » / WhatsApp — 60 requêtes par minute et par adresse
Route::post('/statistiche/clic', [StatsController::class, 'click'])
    ->middleware('throttle:60,1')
    ->name('stats.click');

// Pages de contenu (F-PAGE) — sprint 8
Route::get('/chi-siamo', [ContentPageController::class, 'about'])->name('about');
Route::get('/consegna', [ContentPageController::class, 'delivery'])->name('delivery');
Route::get('/faq', FaqController::class)->name('faq');

// Page provisoire, remplacée au sprint 9
Route::get('/contatti', PlaceholderController::class)->name('contact');

// Pages légales : uniquement les 5 adresses prévues (R-LEG-01) — à garder EN DERNIER
Route::get('/{slug}', LegalPageController::class)
    ->whereIn('slug', LegalPageController::SLUGS)
    ->name('legal.show');
