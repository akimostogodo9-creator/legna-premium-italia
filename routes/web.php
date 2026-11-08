<?php

use App\Http\Controllers\Public\HomeController;
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

// Pages provisoires, remplacées au fil des sprints (S8 : chi-siamo, consegna, faq ; S9 : contatti)
Route::get('/chi-siamo', PlaceholderController::class)->name('about');
Route::get('/consegna', PlaceholderController::class)->name('delivery');
Route::get('/contatti', PlaceholderController::class)->name('contact');
Route::get('/faq', PlaceholderController::class)->name('faq');
