<?php

use App\Http\Controllers\Web\CustomerWebController;
use App\Http\Controllers\Web\PromoWebController;
use App\Http\Controllers\Web\TrackWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TrackWebController::class, 'index'])->name('track.index');
Route::post('/track', [TrackWebController::class, 'track'])->name('track.search');

Route::get('customers/lookup', [CustomerWebController::class, 'lookup'])->name('customers.lookup');
Route::resource('customers', CustomerWebController::class);
Route::resource('promos', PromoWebController::class);
