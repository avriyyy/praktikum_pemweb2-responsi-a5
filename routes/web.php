<?php

use App\Http\Controllers\Web\CustomerWebController;
use App\Http\Controllers\Web\PromoWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('customers/lookup', [CustomerWebController::class, 'lookup'])->name('customers.lookup');
Route::resource('customers', CustomerWebController::class);
Route::resource('promos', PromoWebController::class);
