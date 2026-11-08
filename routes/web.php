<?php

use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PlaceholderController;
use Illuminate\Support\Facades\Route;

/*
| Site public — URLs en italien (étape 3 §2.1)
| L'administration est dans routes/admin.php (/admin).
*/
Route::get('/', HomeController::class)->name('home');

// Pages provisoires, remplacées au fil des sprints (S5 : prodotti ; S8 : chi-siamo, consegna, faq ; S9 : contatti)
Route::get('/prodotti', PlaceholderController::class)->name('products.index');
Route::get('/chi-siamo', PlaceholderController::class)->name('about');
Route::get('/consegna', PlaceholderController::class)->name('delivery');
Route::get('/contatti', PlaceholderController::class)->name('contact');
Route::get('/faq', PlaceholderController::class)->name('faq');
