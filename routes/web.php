<?php

use App\Http\Controllers\Web\CustomerWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('customers/lookup', [CustomerWebController::class, 'lookup'])->name('customers.lookup');
Route::resource('customers', CustomerWebController::class);
